<?php

namespace App\Actions\Reservation;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ExpireReservations
{
    public function execute(): int
    {
        return DB::transaction(function (): int {
            return Reservation::query()
                ->where('status', 'pending')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->update([
                    'status' => 'expired',
                    'updated_at' => now(),
                ]);
        });
    }
}
