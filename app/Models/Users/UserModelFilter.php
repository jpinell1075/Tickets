<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;
use App\Models\User;

class UserModelFilter extends Model
{
    use UserStampsTrait;
    protected $table = 'user_model_filters';

    protected $fillable = [
        'user_id',
        'description',
        'model',
        'field',
        'operator',
        'value',
        'comparison_type',
        'relation',
        'extra',
    ];

    protected $appends = [
        'formatted_extra'
    ];

    public function getFormattedExtraAttribute()
    {
        return json_decode($this->extra, true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
