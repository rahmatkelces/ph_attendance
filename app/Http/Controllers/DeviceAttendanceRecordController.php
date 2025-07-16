<?php

namespace App\Http\Controllers;

use App\Models\DeviceAttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DeviceAttendanceRecordController extends Controller
{
    public function store(Request $request)
    {
        // Log the incoming request data
        Log::info('Store method called', ['request' => $request->all()]);

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new attendance record
        $attendanceRecord = DeviceAttendanceRecord::create($request->all());

        // Log the created record
        Log::info('Record created successfully', ['record' => $attendanceRecord]);

        return response()->json(['success' => true, 'record' => $attendanceRecord], 201);
    }

    public function sendData()
{
    $yesterday = now()->subDay()->startOfDay(); // 2025-07-13 00:00:00
    $today = now()->endOfDay();                 // 2025-07-14 23:59:59

    $records = DeviceAttendanceRecord::whereBetween('timestamp', [$yesterday, $today])
        ->whereNotNull('name')
        ->get();

    // Log::info('Hasil records:', ['records' => $records]);

    // $startDate = Carbon::now()->subDays(7)->startOfDay(); // 7 hari lalu
    // $endDate = Carbon::now()->endOfDay();                 // hari ini

    // $records = DeviceAttendanceRecord::whereBetween('timestamp', [$startDate, $endDate])
    //     ->whereNotNull('name')
    //     ->where('card_no', 2010020176)
    //     ->get();


    $data = [];

    foreach ($records as $record) {
        // Initialize prefix
        $prefix = '';
    
        // Check for prefix in name and assign the correct prefix
        // if (strpos($record->pin, 'PH') === 0) {
        //     $prefix = '1'; // Replace 'PH' with '1'
        // } elseif (strpos($record->pin, 'PT') === 0) {
        //     $prefix = '2'; // If you also have 'PT', replace it with '2' (optional)
        // }
    
        // Get the part of the name after 'PH' or 'PT' and remove the '20'
        // $nikBody = substr($record->pin, 2); // Get '2004010084'
        // $nikBodyWithout20 = substr($nikBody, 2); // Remove the first two characters, giving '04010084'
    
        // Combine prefix with the adjusted body
        $data[] = [
            'nik' => $prefix . $record->employee_id, // Concatenate the prefix with the modified body
            'date' => $record->timestamp->format('Y-m-d'), // Get the date
            'time' => $record->timestamp->format('H:i:s'), // Get the time
            'type_check_in_check_out' => $record->type, // Set type as 'type_check_in_check_out'
        ];
    }
    

    // Log data yang akan dikirim
    // Log::info('Data to be sent to external API', ['data' => $data]);

    // Kirim data ke API eksternal
    $response = Http::withHeaders([
        'X-API-Key' => 'DRJKc/lUf+hHzP71l5eKOA==|oEUm+dSOYky5KatEMs9J9w==',
        'Accept' => 'application/json',
    ])->withBody(json_encode($data), 'application/json')
      ->post('https://apiservicestandalone.powerplus10.com/powerplus/sta-pizza/add-attendance-machine');    

    // Log respons
    // Log::info('Response from external API', ['response' => $response->body()]);

    // Periksa apakah ada kesalahan
    if ($response->failed()) {
        Log::error('Failed to send data', ['status' => $response->status(), 'body' => $response->body()]);
        return response()->json(['success' => false, 'message' => 'Failed to send data'], 500);
    }
    // Log::error('Failed to send data', [
    //     'status' => $response->status(),
    //     'body' => $response->body(),
    // ]);
    
    return response()->json(['success' => true, 'message' => 'Data sent successfully', 'response' => $response->json()], 200);
}

}
