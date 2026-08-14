<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
        'university_id',
        'name',
        'slug',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}
