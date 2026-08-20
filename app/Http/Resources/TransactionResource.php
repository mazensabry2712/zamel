<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_id,
            'listing_id' => $this->listing_id,

            'amount' => $this->amount,
            'platform_buyer_fee' => $this->platform_buyer_fee,
            'platform_seller_fee' => $this->platform_seller_fee,
            'total_amount' => $this->total_amount,
            'seller_amount' => $this->seller_amount,

            'status' => $this->status,

            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
        ];
    }
}
