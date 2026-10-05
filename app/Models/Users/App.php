<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Everth\UserStamps\UserStampsTrait;

class App extends Model
{
    use UserStampsTrait;

    protected $fillable = [
        'name',
        'internal_name',
        'access_route_name',
        'icon',
        'order',
        'show_in_menu',
    ];

    /**
     * Defines a one-to-many relationship with the Module model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
