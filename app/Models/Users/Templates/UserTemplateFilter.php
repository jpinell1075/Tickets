<?php

namespace App\Models\Users\Templates;

use Illuminate\Database\Eloquent\Model;

class UserTemplateFilter extends Model
{
    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_template_id',
        'model',
        'field',
        'operator',
        'value',
        'comparison_type',
        'relation',
        'extra',
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
}
