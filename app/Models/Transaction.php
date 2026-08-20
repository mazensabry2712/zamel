<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'listing_id',
        'buyer_id',
        'seller_id',
        'amount',
        'platform_buyer_fee',
        'platform_seller_fee',
        'total_amount',
        'seller_amount',
        'status',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_buyer_fee' => 'decimal:2',
        'platform_seller_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
