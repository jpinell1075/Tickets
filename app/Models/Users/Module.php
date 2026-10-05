<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;
use App\Traits\TableFormData\Module as TableFormDataModule;

/**
 * Class Module
 * Represents the Module model, which defines the modules available in the system.
 * This model handles module-related data and relationships.
 */
class Module extends Model
{
    use HasFactory;
    use UserStampsTrait;
    use TableFormDataModule;

    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'internal_name',
        'access_route_name',
        'icon',
        'order',
        'show_in_menu',
        'app_id'
    ];

    /**
     * Defines a one-to-many relationship with the UserModule model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userModule()
    {
        return $this->hasMany(UserModule::class);
    }

    /**
     * Defines a belongs-to relationship with the App model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function app()
    {
        return $this->belongsTo(App::class);
    }
}
