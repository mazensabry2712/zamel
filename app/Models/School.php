<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name',
        'city',
    ];

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}
