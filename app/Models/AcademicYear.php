<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'education_type',
        'sort_order',
    ];

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}
