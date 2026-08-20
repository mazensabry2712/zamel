<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function show(Transaction $transaction): TransactionResource
    {
        $this->authorize('view', $transaction);

        return new TransactionResource($transaction);
    }
}
