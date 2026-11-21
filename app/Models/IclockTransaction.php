<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IclockTransaction extends Model
{
    protected $connection = 'pgsql_biotime';

    protected $table = 'iclock_transaction';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'emp_code',
        'punch_time',
        'punch_state',
        'is_sent',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'is_sent' => 'integer',
    ];
}