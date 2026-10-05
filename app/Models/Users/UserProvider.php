<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;
use App\Models\User;
use App\Traits\ModelSoftDeleteTrait;

class UserProvider extends Model
{
    use UserStampsTrait, ModelSoftDeleteTrait;

    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_id',
        'provider_email',
    ];

    protected $appends = [
        'provider_icon',
    ];

    public function getProviderIconAttribute()
    {
        $icons = [
            'google' => 'bx bxl-google',
            'facebook' => 'bx bxl-facebook-square',
            'github' => 'bx bxl-github',
        ];

        return $icons[$this->provider_name] ?? 'fas fa-user';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
