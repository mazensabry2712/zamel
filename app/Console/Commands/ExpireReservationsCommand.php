<?php

namespace App\Console\Commands;

use App\Actions\Reservation\ExpireReservations;
use Illuminate\Console\Command;

class ExpireReservationsCommand extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Expire pending reservations whose expiry time has passed';

    public function handle(ExpireReservations $expireReservations): int
    {
        $expired = $expireReservations->execute();

        $this->info("Expired {$expired} reservation(s).");

        return self::SUCCESS;
    }
}
