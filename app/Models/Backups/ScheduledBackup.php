<?php

namespace App\Models\Backups;

use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;

class ScheduledBackup extends Model
{
    use UserStampsTrait;

    protected $fillable = [
        'days',
        'times',
        'active',
    ];

    protected $casts = [
        'days' => 'array',
        'times' => 'array',
        'active' => 'boolean',
    ];
}
