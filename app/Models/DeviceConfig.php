<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceConfig extends Model
{
    protected $table = 'device_configs';

    protected $fillable = ['key', 'name', 'value', 'is_active'];

    public $timestamps = false; // ← Tambahkan baris ini
}

