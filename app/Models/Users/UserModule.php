<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;
use App\Models\User;
use App\Traits\TableFormData\UserModule as TableFormDataUserModule;

/**
 * Class UserModule
 * Represents the UserModule model, which links users to modules.
 * This model handles the relationship between users and their assigned modules.
 */
class UserModule extends Model
{
    use HasFactory;
    use UserStampsTrait;
    use TableFormDataUserModule;

    /**
     * @var string The name of the table associated with the model.
     */
    protected $table = 'users_modules';

    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'module_id',
    ];

    /**
     * Defines a relationship to the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Defines a relationship to the Module model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Defines a relationship to the UserModuleAction model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany(UserModuleAction::class, 'user_module_id');
    }
}
