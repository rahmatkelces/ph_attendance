@extends('layout.layout')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-2">
        <div class="p-6">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                <div class="ml-4 text-lg leading-7 font-semibold"><a href="#" class=" text-gray-900 dark:text-white">
                <b>Pizzahut Indonesia</b>
                </a></div>
            </div>
            <br/>
            <div class="ml-12">
                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                    <img src="{{ asset('machine.jpg') }}" style="width: 100%;"/>
                </div>
            </div>
        </div>

        <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l">
            <div class=" ">
                <a href="{{ route('machine.home') }}" class="btn btn-success" style="float:right">
                    Back to home
                </a>
            </div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://ph_posmanager.phsmk.id" class="underline text-gray-900 dark:text-white">Device data</a></div>
            </div>
            
            <hr/>
            <div class="">
                <br/>
                <div class="flex items-center">
                    <img src="{{ asset('project.gif') }}" style="width: 150%;"/>
                </div>
            </div>
            
        </div>

    </div>
</div>

<div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-1">
        

        <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l">
            <div class=" ">
                <a href="{{ route('machine.devicedata') }}" class="btn btn-success" style="float:right">
                    Reload
                </a>
            </div>
            <div class="flex items-center">
                <br/><br/>
                
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://ph_posmanager.phsmk.id" class="underline text-gray-900 dark:text-white">All data from device : <b>Users</b></a></div>
            </div>
            <hr/>
            <div class="ml-12">
                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                    <table class="table table-bordered table-responsive">
                        <thead>
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">UID</th>
                            <th scope="col">User ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Role</th>
                            <th scope="col">Password</th>
                            <th scope="col">Card No</th>
                            <th scope="col">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                            @php 
                            $sl=1;
                            @endphp

                          @foreach($users as $user)  
                          <tr>
                            <th scope="row">{{ $sl++ }}</th>
                            <td>{{ $user['uid'] }}</td>
                            <td>{{ $user['userid'] }}</td>
                            <td>{{ $user['name'] }}</td>
                            <td>{{ $user['role'] == 14 ? 'Administrator' : 'User' }}</td>
                            <td>
                                <span class="password-hidden" id="pwd-{{ $sl }}">{{ str_repeat('*', strlen($user['password'])) }}</span>
                                <span class="password-visible d-none" id="pwd-show-{{ $sl }}">{{ $user['password'] }}</span>
                                <button type="button" onclick="togglePassword({{ $sl }})" class="btn btn-sm btn-light">
                                    👁️
                                </button>
                            </td>

                            <td>{{ $user['cardno'] }}</td>
                            <td>
                                <a class="btn btn-warning btn-sm" href="{{ route('machine.deviceeditusersingle', $user['uid']) }}">Edit</a>
                                <a class="btn btn-success btn-sm" href="{{ route('machine.deviceviewusersingle',$user) }}">View</a>
                                <a href="{{ route('machine.deviceremoveusersingle', $user['uid']) }}"
                                    class="btn btn-danger btn-sm btn-delete"
                                    data-url="{{ route('machine.deviceremoveusersingle', $user['uid']) }}">
                                    Delete
                                </a>
                            </td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                </div>
            </div>
            
        </div>

    </div>
</div>

<div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-1">
        

        <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l">
            <div class=" ">
                <!-- <a href="{{ route('machine.devicedata.clear.attendance') }}" class="btn btn-success " style="float:right;margin-left: 5px">Clear attendance</a> -->
                <a href="{{ route('machine.devicedata') }}" class="btn btn-success" style="float:right">
                    Reload
                </a>
            </div>
            <div class="flex items-center">
                <br/><br/>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://ph_posmanager.phsmk.id" class="underline text-gray-900 dark:text-white">All data from device : <b>Attendance</b></a></div>
            </div>
            <hr/>
            <div class="ml-12">
                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                    <table class="table table-bordered table-responsive">
                        <thead>
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">UID</th>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">State</th>
                            <th scope="col">Timestamp</th>
                            <th scope="col">Type
                                <br>
                                0=checkin 
                                <br>
                                1=checkout
                            </th>
                            <th scope="col">Card No</th>
                          </tr>
                        </thead>
                        <tbody>
                            @php 
                            $sl=1;
                            @endphp
                          
                            @foreach($attendances as $attendance) 
<tr>
    <th scope="row">{{ $sl++ }}</th>
    <td>{{ $attendance['uid'] }}</td>
    <td>{{ $attendance['id'] }}</td>
    <td>
        @php
            $userCardNo = isset($users[$attendance['id']]) ? $users[$attendance['id']]['name'] : 'N/A';
        @endphp
        {{ $userCardNo }}
    </td>
    <td>{{ $attendance['state'] }}</td>
    <td>{{ $attendance['timestamp'] }}</td>
    <td>{{ $attendance['type'] }}</td>
    <td>
        @php
            $userCardNo = isset($users[$attendance['id']]) ? $users[$attendance['id']]['cardno'] : 'N/A';
        @endphp
        {{ $userCardNo }}
    </td>
</tr>
@endforeach

                        </tbody>
                      </table>
                </div>
            </div>
            
        </div>

    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.btn-delete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('data-url');

            Swal.fire({
                title: 'Enter PIN to Delete',
                input: 'password',
                inputLabel: 'PIN',
                inputPlaceholder: 'Enter PIN to proceed',
                inputAttributes: {
                    maxlength: 10,
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel',
                preConfirm: (pin) => {
                    if (pin !== '1500600') {
                        Swal.showValidationMessage('Incorrect PIN');
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
});


function togglePassword(index) {
        const hidden = document.getElementById('pwd-' + index);
        const shown = document.getElementById('pwd-show-' + index);

        if (hidden.classList.contains('d-none')) {
            hidden.classList.remove('d-none');
            shown.classList.add('d-none');
        } else {
            hidden.classList.add('d-none');
            shown.classList.remove('d-none');
        }
    }
</script>

@endsection