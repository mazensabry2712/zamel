<?php

namespace App\Actions\MarketplaceRequest;

use App\Models\MarketplaceRequest;

class DeleteMarketplaceRequest
{
    public function execute(MarketplaceRequest $marketplaceRequest): void
    {
        $marketplaceRequest->delete();
    }
}
