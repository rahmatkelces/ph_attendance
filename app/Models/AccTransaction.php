<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccTransaction extends Model
{
    protected $connection = 'mysql';

    protected $table = 'acc_transaction';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'app_id',
        'bio_tbl_id',
        'company_id',
        'create_time',
        'creater_code',
        'creater_id',
        'creater_name',
        'op_version',
        'update_time',
        'updater_code',
        'updater_id',
        'updater_name',
        'acc_zone',
        'acc_zone_code',
        'area_name',
        'card_no',
        'dept_code',
        'dept_name',
        'description',
        'dev_alias',
        'dev_id',
        'dev_sn',
        'event_addr',
        'event_name',
        'event_no',
        'event_point_id',
        'event_point_name',
        'event_point_type',
        'event_time',
        'last_name',
        'log_id',
        'mask_flag',
        'name',
        'pin',
        'reader_name',
        'reader_state',
        'temperature',
        'trigger_cond',
        'unique_key',
        'verify_mode_name',
        'verify_mode_no',
        'vid_linkage_handle',
        'is_sent',
    ];

    protected $casts = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'event_time' => 'datetime',
        'is_sent' => 'integer',
    ];

    public $timestamps = false;
}