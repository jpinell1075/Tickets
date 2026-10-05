<?php

namespace App\Models\Users\Templates;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TableFormData\UserTemplate as TableFormDataUserTemplate;

class UserTemplate extends Model
{
    use TableFormDataUserTemplate;
    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Defines a one-to-many relationship with the UserTemplateModule model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modules()
    {
        return $this->hasMany(UserTemplateModule::class);
    }

    /**
     * Defines a one-to-many relationship with the UserTemplateFilter model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function filters()
    {
        return $this->hasMany(UserTemplateFilter::class);
    } 
}
