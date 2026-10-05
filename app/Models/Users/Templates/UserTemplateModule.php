<?php

namespace App\Models\Users\Templates;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\Module;

class UserTemplateModule extends Model
{
    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_template_id',
        'module_id',
    ];

    /**
     * Defines a many-to-one relationship with the UserTemplate model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userTemplate()
    {
        return $this->belongsTo(UserTemplate::class);
    }

    /**
     * Defines a many-to-one relationship with the Module model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Defines a one-to-many relationship with the UserTemplateModuleAction model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany(UserTemplateModuleAction::class);
    }
}
