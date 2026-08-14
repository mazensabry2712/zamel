<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];
    public function faculties()
    {
        return $this->hasMany(Faculty::class);
    }
    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}
