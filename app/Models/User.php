<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Users\App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Users\UserModule;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Users\Module;
use App\Models\Users\UserProvider;
use App\Traits\TableFormData\User as TableFormDataUser;
use App\Models\Users\UserModelFilter;
use App\Traits\ModelFilters\HasUserModelFilters;
use App\Traits\ModelSoftDeleteTrait;
use App\Traits\AiTraits\UserAiTrait;
use App\Models\Areas\Area;
use App\Models\Equipos\Equipo;

/**
 * Class User
 * Represents the User model, which extends the Authenticatable class.
 * This model handles user-related data and relationships.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, 
        TableFormDataUser, HasUserModelFilters,
        ModelSoftDeleteTrait, UserAiTrait;

    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 0: Administrator, 1: User
        'it_staff', // Indicates if the user is part of the IT staff
        'can_login', // Indicates if the user can log in

        'nombre',
        'telefono',
        'especialidad',
        'estado',
        'id_area',
    ];

    /**
     * @var array<int, string> The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string> The attributes and their cast types.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'it_staff' => 'boolean',
        ];
    }

    /**
     * @var string The available model filters.
     * This array defines the filters that can be applied to the User model.
     */
    public static $available_model_filters = [
        'simple' => [
            'label' => 'Filtros simples',
            'type' => self::TYPE_SIMPLE,
            'fields' => [
                'role' => [
                    'label' => 'Rol',
                    'type' => self::TYPE_STATIC_SELECT,
                    'values' => [
                        '0' => 'Administrador',
                        '1' => 'Usuario',
                    ],
                ],
            ],
            'operators' => [
                self::OP_EQUAL => [
                    'label' => 'Igual',
                    'value' => self::OP_EQUAL,
                ],
                self::OP_NOT_EQUAL => [
                    'label' => 'Diferente',
                    'value' => self::OP_NOT_EQUAL,
                ],
            ],
        ],
        'user_own' => [
            'label' => 'Pertenece al usuario',
            'type' => self::TYPE_USER_OWN,
            'relation_name' => null,
            'foreign_key' => 'id',
        ],
    ];

    /**
     * Defines a one-to-many relationship with the UserProvider model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userProviders()
    {
        return $this->hasMany(UserProvider::class);
    }

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
     * Defines a one-to-many relationship with the UserModelFilter model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userModelFilters()
    {
        return $this->hasMany(UserModelFilter::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    /**
     * Retrieves the applications associated with the user.
     * If the user is an administrator, all applications are returned.
     *
     * @return \Illuminate\Database\Eloquent\Collection The collection of applications.
     */
    public function userApps()
    {
        if($this->isAdmin()) {
            return App::all();
        }

        $apps = new Collection();

        App::whereHas('modules.userModule', function($query) {
            $query->where('user_id', $this->id)
            ->whereHas('actions', function($query) {
                $query->where('action', 'read');
            });
        })->get()->each(function($app) use ($apps) {
            $apps->push($app);
        });

        return $apps;
    }

    /**
     * Retrieves the modules associated with the user.
     * If the user is an administrator, all modules are returned.
     *
     * @return \Illuminate\Database\Eloquent\Collection The collection of modules.
     */
    public function modules()
    {
        if($this->isAdmin()) {
            return Module::all();
        }

        $modules = new Collection();

        UserModule::where('user_id', $this->id)
        ->whereHas('actions', function($query) {
            $query->where('action', 'read');
        })
        ->get()
        ->each(function($userModule) use ($modules) {
            $modules->push($userModule->module);
        });

        return $modules;
    }

    
    /**
     * Checks if the user has access to a specific module and method.
     *
     * @return bool True if the user is an administrator or has access to the module and method, false otherwise.
     */
    public function hasAccessToModule(string $module, string $method): bool
    {
        if($this->isAdmin()) return true;

        $action_names = [
            'GET' => 'read',
            'HEAD' => 'read',
            'OPTIONS' => 'read',
            'POST' => 'create',
            'PUT' => 'update',
            'PATCH' => 'update',
            'DELETE' => 'delete',
            'SEARCH' => 'search',
        ];

        return $this->userModule()->whereHas('module', function($query) use ($module) {
            $query->where('internal_name', $module);          
        })
        ->whereHas('actions', function($query) use ($method, $action_names) {
            $query->where('action', $action_names[$method]);
        })
        ->exists();
    }

    /**
     * Checks if the user has an administrator role.
     *
     * @return bool True if the user is an administrator, false otherwise.
     */
    public function isAdmin(): bool
    {
        return $this->role === '0';
    }

    /**
     * Checks if the user has a regular user role.
     *
     * @return bool True if the user is a regular user, false otherwise.
     */
    public function isUser(): bool
    {
        return $this->role === '1';
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_usuario');
    }
}
