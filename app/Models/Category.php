<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'seo_title',
        'seo_description',
        'is_active',
        'status',
        'created_by',
        'moderation_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
