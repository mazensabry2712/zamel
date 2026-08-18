<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'budget',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
