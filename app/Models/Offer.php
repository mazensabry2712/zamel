<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'price',
        'condition',
        'message',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function marketplaceRequest()
    {
        return $this->belongsTo(MarketplaceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
