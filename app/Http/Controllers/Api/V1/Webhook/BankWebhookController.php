<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankWebhookController extends Controller
{
    public function handle(Request $request, string $token): JsonResponse
    {
        $bankAccount = BankAccount::where('webhook_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$bankAccount) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        $payload = $request->all();

        Log::info('Bank webhook received', [
            'bank' => $bankAccount->bank_code,
            'merchant_id' => $bankAccount->merchant_id,
        ]);

        // Check for duplicate
        if (!empty($payload['reference'])) {
            $exists = Transaction::where('bank_account_id', $bankAccount->id)
                ->where('bank_reference', $payload['reference'])
                ->exists();

            if ($exists) {
                return response()->json(['status' => 'duplicate'], 200);
            }
        }

        Transaction::create([
            'merchant_id'        => $bankAccount->merchant_id,
            'bank_account_id'    => $bankAccount->id,
            'source_type'        => 'bank_api',
            'status'             => 'verified',
            'amount'             => $payload['amount'] ?? 0,
            'currency'           => $payload['currency'] ?? 'ETB',
            'sender_name'        => $payload['sender_name'] ?? null,
            'sender_phone'       => $payload['sender_phone'] ?? null,
            'sender_account'     => $payload['sender_account'] ?? null,
            'bank_name'          => $bankAccount->bank_name,
            'bank_code'          => $bankAccount->bank_code,
            'bank_reference'     => $payload['reference'] ?? null,
            'raw_payload'        => $payload,
            'ai_confidence_score'=> 99,
            'verified_at'        => now(),
        ]);

        return response()->json(['status' => 'received'], 200);
    }
}
