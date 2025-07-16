@extends('layout.layout')

@section('content')
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
            <a href="{{ route('machine.home') }}" class="btn btn-success" style="float:right">
                Back to home
            </a>

            <div class="flex items-center">
                <br/><br/>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://ph_posmanager.phsmk.id" class="underline text-gray-900 dark:text-white">Add user to device</a></div>
            </div>
            <hr/>
            <div class="ml-12">
            <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                <form action="{{ route('machine.devicesetuser') }}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="uid" class="col-3 col-form-label">UID :</label>
                        <div class="col-9">
                            <!-- <input type="text" name="uid" id="uid" class="form-control" placeholder="Enter UID" required /> -->
                            <input type="text" name="uid" id="uid" class="form-control" value="{{ $lastUid }}" disabled />
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="userid" class="col-3 col-form-label">User ID :</label>
                        <div class="col-9">
                            <input type="text" name="userid" id="userid" class="form-control" placeholder="Enter User ID" required />
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="name" class="col-3 col-form-label">Name :</label>
                        <div class="col-9">
                            <input type="text" name="name" id="name" class="form-control" placeholder="Enter Name" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="role" class="col-3 col-form-label">Role :</label>
                        <div class="col-9">
                            <select name="role" id="role" class="form-control" required>
                                <option value="0">User</option>
                                <option value="14">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="password" class="col-3 col-form-label">Password :</label>
                        <div class="col-9">
                            <input type="text" name="password" id="password" class="form-control" placeholder="Enter Password (optional)" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="cardno" class="col-3 col-form-label">Card No :</label>
                        <div class="col-9">
                            <input type="text" name="cardno" id="cardno" class="form-control" placeholder="Enter Card No" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-9 offset-3">
                            <button type="submit" class="btn btn-success" style="width: 100%">Submit</button>
                        </div>
                    </div>
                </form>
            </div>

            </div>
            
        </div>

    </div>
</div>
@endsection