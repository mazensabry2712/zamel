<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'user_id',
        'offer_id',
        'status',
        'reserved_at',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'expires_at',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
