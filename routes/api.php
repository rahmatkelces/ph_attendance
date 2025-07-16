<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceAttendanceRecordController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/device-attendance-records', [DeviceAttendanceRecordController::class, 'store']);
Route::post('/send-attendance-data', [DeviceAttendanceRecordController::class, 'sendData']);
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
