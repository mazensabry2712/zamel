<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Listing;
class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'seo_title',
        'seo_description',
        'is_active',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function listings(){
        return $this->hasMany(Listing::class);
    }
}
