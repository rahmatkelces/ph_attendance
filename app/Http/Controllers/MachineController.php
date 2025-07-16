<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceAttendanceRecord;
use App\Models\DeviceConfig;


use Illuminate\Http\Request;
use Rats\Zkteco\Lib\ZKTeco;

class MachineController extends Controller
{
    /**
     * Get the device IP from config table.
     *
     * @return string
     */
    public function device_ip()
    {
        $ipConfig = DeviceConfig::where('key', 'ip_mesin')->where('is_active', 1)->first();
        return $ipConfig ? $ipConfig->value : '127.0.0.1';
    }

    /**
     * Set the device IP in config table (optional if needed).
     */
    public function device_setip(Request $request)
    {
        DeviceConfig::updateOrCreate(
            ['key' => 'ip_mesin'],
            [
                'name' => 'Ip Mesin',
                'value' => $request->deviceip,
                'is_active' => 1,
            ]
        );

        return redirect()->back()->with('success_message', 'IP updated.');
    }

    /**
     * Display the welcome page with device IP.
     */
    public function index()
    {
        $deviceip = $this->device_ip();
        return view('welcome', compact('deviceip'));
    }

    // ... SELURUH METHOD LAINNYA TIDAK PERLU DIUBAH karena semua ambil dari $this->device_ip()
    // (sudah otomatis ambil dari table `device_configs` karena fungsi device_ip() sudah diperbaiki)

    /**
     * Test sound on the device.
     *
     * @return \Illuminate\Http\Response
     */
    public function test_sound()
    {
        $deviceip = $this->device_ip();
        $zk = new ZKTeco($deviceip, 4370);
        
        if ($zk->connect()) {
            $zk->disableDevice();
            try {
                $zk->testVoice();
                $zk->enableDevice();
                return redirect()->back()->with('success_message', 'Playing sound on device.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error_message', 'Failed to play sound: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error_message', 'Failed to connect to device.');
        }
    }

    /**
     * Get device information.
     *
     * @return \Illuminate\Http\Response
     */
    public function device_information()
    {
        $deviceip = $this->device_ip();
        $zk = new ZKTeco($deviceip, 4370);
        
        if ($zk->connect()) {
            $zk->disableDevice();

            try {
                // Retrieve device information
                $deviceVersion = $zk->version();
                $deviceOSVersion = $zk->osVersion();
                $devicePlatform = $zk->platform();
                $devicefmVersion = $zk->fmVersion();
                $deviceworkCode = $zk->workCode();
                $devicessr = $zk->ssr();
                $devicepinWidth = $zk->pinWidth();
                $deviceserialNumber = $zk->serialNumber();
                $devicedeviceName = $zk->deviceName();
                $devicegetTime = $zk->getTime();

                // Enable the device again after retrieving data
                $zk->enableDevice();
                $zk->disconnect();

                return view('device-information', compact(
                    'deviceip', 'deviceVersion', 'deviceOSVersion', 'devicePlatform', 
                    'devicefmVersion', 'deviceworkCode', 'devicessr', 'devicepinWidth', 
                    'deviceserialNumber', 'devicedeviceName', 'devicegetTime'
                ));
            } catch (\Exception $e) {
                return redirect()->back()->with('error_message', 'Failed to retrieve device information: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error_message', 'Failed to connect to device.');
        }
    }

    /**
     * Get device data (users and attendance).
     *
     * @return \Illuminate\Http\Response
     */
    public function device_data()
{
    set_time_limit(300); 

    $deviceip = $this->device_ip();
    $zk = new ZKTeco($deviceip, 4370);
    
    if ($zk->connect()) {
        $zk->disableDevice();
        
        try {
            // Retrieve user and attendance data
            $users = $zk->getUser();
            // Log::info('Users data retrieved', ['users' => $users]);

            $attendances = $zk->getAttendance();
            // Log::info('Attendance data retrieved', ['attendances' => $attendances]);

            // Process and save attendance records to the database
            foreach ($attendances as $attendance) {
                // Log::info('Processing attendance record', ['attendance' => $attendance]);

                $userCardNo = isset($users[$attendance['id']]) ? $users[$attendance['id']]['cardno'] : 'N/A';
                $userName   = isset($users[$attendance['id']]) ? $users[$attendance['id']]['name'] : 'N/A';
                $userPin    = isset($users[$attendance['id']]) ? $users[$attendance['id']]['password'] : null;
                // Log::info('password = ' . $userPin);

                if ($userCardNo !== 'N/A') {
                    $attendanceRecord = DeviceAttendanceRecord::where('uid', $attendance['id'])
                        ->where('timestamp', $attendance['timestamp'])
                        ->where('type', $attendance['type'])
                        ->first();
                
                        Log::info('NAmenya siapa' . $attendanceRecord);
                    if ($attendanceRecord) {
                        // Log::info('NAmenya siapa', ['isinya' => $userName]);
                        $attendanceRecord->update([
                            'card_no'   => $userCardNo,
                            'name'      => $userName,
                            'pin'       => $userPin,
                            'state'     => $attendance['state'] ?? null,
                            'timestamp' => $attendance['timestamp'],
                            'type'      => $attendance['type'] ?? null,
                        ]);
                        // Log::info('Record updated successfully', ['record' => $attendanceRecord]);
                    } else {
                        $data = [
                            'card_no'     => $userCardNo,
                            'name'        => $userName,
                            'pin'         => $userPin,
                            'uid'         => $attendance['id'],
                            'employee_id' => $attendance['id'] ?? null,
                            'state'       => $attendance['state'] ?? null,
                            'timestamp'   => $attendance['timestamp'] ?? null,
                            'type'        => $attendance['type'] ?? null,
                        ];

                        DeviceAttendanceRecord::create($data);
                        // Log::info('Record created successfully', ['record' => $data]);
                    }
                } else {
                    Log::error('Missing card_no or name for UID', ['uid' => $attendance['id']]);
                }
            }

            $zk->enableDevice();
            $zk->disconnect();

            return view('device-data', compact('deviceip', 'users', 'attendances'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Failed to retrieve data: ' . $e->getMessage());
        }
    } else {
        return redirect()->back()->with('error_message', 'Failed to connect to device.');
    }
}


    /**
     * Clear attendance data on the device.
     *
     * @return \Illuminate\Http\Response
     */
    public function device_data_clear_attendance()
    {
        $deviceip = $this->device_ip();
        $zk = new ZKTeco($deviceip, 4370);
        
        if ($zk->connect()) {
            $zk->disableDevice();  
            $zk->clearAttendance();
            return redirect()->back()->with('success_message', 'Attendance cleared successfully.');
        } else {
            return redirect()->back()->with('error_message', 'Failed to connect to device.');
        }
    }

    /**
     * Restart the device.
     *
     * @return \Illuminate\Http\Response
     */
    public function device_restart()
    {
        $deviceip = $this->device_ip();
        $zk = new ZKTeco($deviceip, 4370);
        
        if ($zk->connect()) {
            $zk->disableDevice();  
            $zk->restart();
            return redirect()->back()->with('success_message', 'Device restarted successfully.');
        } else {
            return redirect()->back()->with('error_message', 'Failed to connect to device.');
        }
    }

    /**
     * Shutdown the device.
     *
     * @return \Illuminate\Http\Response
     */
    public function device_shutdown()
    {
        $deviceip = $this->device_ip();
        $zk = new ZKTeco($deviceip, 4370);
        
        try {
            if ($zk->connect()) {
                $zk->shutdown();
                return redirect()->back()->with('success_message', 'Device shutdown successfully. Please restart the device manually.');
            } else {
                return redirect()->back()->with('error_message', 'Failed to connect to device.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'An error occurred while shutting down the device: ' . $e->getMessage());
        }
    }

    /**
     * Show the form to add a user.
     *
     * @return \Illuminate\Http\Response
     */
    // public function device_adduser()
    // {
    //     $deviceip = $this->device_ip();
    //     return view('device-adduser', compact('deviceip'));
    // }

        public function device_adduser()
        {
            $deviceip = $this->device_ip();
            $zk = new ZKTeco($deviceip);

            $lastUid = 1; // default kalau belum ada user

            if ($zk->connect()) {
                $users = $zk->getUser();
                $zk->disconnect();

                if ($users && count($users)) {
                    $uids = array_column($users, 'uid');
                    $lastUid = max($uids) + 1;
                }
            }

            return view('device-adduser', compact('deviceip', 'lastUid'));
        }


    /**
     * Set a user on the device.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function device_setuser(Request $request)
    {
        $deviceip = $this->device_ip();
        $zk = new ZKTeco($deviceip, 4370);

        try {
            if ($zk->connect()) {
                $zk->disableDevice();  

                // Memastikan format input yang benar
                $uid = trim($request->uid);
                $userid = trim($request->userid);
                $name = trim($request->name);
                $role = (int)trim($request->role);
                $password = trim($request->password);
                $cardno = trim($request->cardno); // Ambil nilai dari input cardno

                if (strlen($cardno) < 10) {
                    // Tambahkan '1' di depan dan pastikan panjang total 10 karakter
                    $cardno = '1' . str_pad($cardno, 9, "0", STR_PAD_LEFT);
                }
                
                // Pastikan panjangnya 10
                $cardno = substr($cardno, 0, 10); // Memastikan panjang tidak lebih dari 10 karakter
                
                // Set user pada perangkat ZKTeco
                $zk->setUser($uid, $userid, $name, $password, $role, $cardno);
                
                $zk->enableDevice();

                return redirect()->back()->with('success_message', 'User added to device successfully.');
            } else {
                return redirect()->back()->with('error_message', 'Failed to connect to device.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'An error occurred: ' . $e->getMessage());
        }
    }



    /**
     * Remove a user from the device by UID.
     *
     * @param string $uid
     * @return \Illuminate\Http\Response
     */
    public function device_removeuser_single($uid)
    {
        $deviceip = $this->device_ip();
        $zk = new ZKTeco($deviceip, 4370);
        
        if ($zk->connect()) {
            $zk->disableDevice();  
            $zk->removeUser($uid);
            return redirect()->back()->with('success_message', 'User removed from device successfully.');
        } else {
            return redirect()->back()->with('error_message', 'Failed to connect to device.');
        }
    }

    /**
     * View user information by UID.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function device_viewuser_single(Request $request)
    {
        $deviceip = $this->device_ip();
        $zk = new ZKTeco($deviceip, 4370);
        
        // Ambil nilai dari request
        $uid = $request->uid;
        $userid = $request->userid;
        $name = $request->name;
        $role = $request->role;
        $password = $request->password;
        $cardno = $request->cardno;

        if ($zk->connect()) {
            $userfingerprints = $zk->getFingerprint($uid);

            // Kirim data ke view
            return view('device-information-user', compact(
                'deviceip', 'uid', 'userid', 'name', 'role', 'password', 'cardno', 'userfingerprints'
            ));
        } else {
            return redirect()->back()->with('error_message', 'Failed to connect to device.');
        }
    }

    public function editUser($uid)
        {
            $users = $this->getAllDeviceUsers(); // ambil dari device

            $user = collect($users)->firstWhere('uid', $uid); // cari berdasarkan UID

            if (!$user) {
                return redirect()->back()->with('error', 'User not found');
            }

            return view('edit_user', compact('user')); // tampilkan form edit
        }

        public function updateUser(Request $request, $uid)
        {
            $request->validate([
                'userid' => 'required',
                'name' => 'required',
                'password' => 'required|numeric',
                'role' => 'required|numeric',
                'cardno' => 'nullable|numeric',
            ]);

            $zk = new ZKTeco($this->device_ip());

            if ($zk->connect()) {
                $zk->setUser(
                    $uid,
                    $request->userid,
                    $request->name,
                    $request->password,
                    $request->role,
                    $request->cardno ?? 0
                );

                $zk->disconnect();
                return redirect()->route('machine.devicedata')->with('success', 'User updated successfully.');
            }

            return redirect()->back()->with('error', 'Failed to connect to device.');
        }

        public function getAllDeviceUsers()
        {
            $zk = new ZKTeco($this->device_ip()); // ambil dari session
            
            if ($zk->connect()) {
                $users = $zk->getUser();
                $zk->disconnect();
                return $users;
            }
        
            return [];
        }        

}
