<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceAttendanceRecord extends Model
{
    use HasFactory;

    // Define the table name if it does not follow the Laravel convention
    protected $table = 'device_attendance_records';

    // Define the fillable attributes to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'card_no',
        'pin',
        'name',
        'uid',
        'employee_id',
        'state',
        'timestamp',
        'type',
    ];

    // If you want to cast certain attributes to specific data types, you can use the $casts property
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define a relationship with the Employee model (assuming an Employee model exists).
     * Adjust the model name and foreign key as necessary.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
