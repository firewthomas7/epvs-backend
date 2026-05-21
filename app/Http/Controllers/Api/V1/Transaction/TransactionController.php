<?php

namespace App\Http\Controllers\Api\V1\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $merchantId = $this->getMerchantId($request);

        $query = Transaction::where('merchant_id', $merchantId)
            ->with(['bankAccount', 'verifiedBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('epvs_ref', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('sender_phone', 'like', "%{$search}%")
                  ->orWhere('bank_reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate($request->integer('per_page', 20));

        return response()->json($transactions);
    }

    public function show(string $uuid): JsonResponse
    {
        $transaction = Transaction::where('uuid', $uuid)
            ->with(['bankAccount', 'verifiedBy', 'voiceAnnouncement'])
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        return response()->json(['transaction' => $transaction]);
    }

    public function stats(Request $request): JsonResponse
    {
        $merchantId = $this->getMerchantId($request);

        $stats = [
            'today_count'  => Transaction::where('merchant_id', $merchantId)->whereDate('created_at', today())->where('status', 'verified')->count(),
            'today_amount' => Transaction::where('merchant_id', $merchantId)->whereDate('created_at', today())->where('status', 'verified')->sum('amount'),
            'total_count'  => Transaction::where('merchant_id', $merchantId)->where('status', 'verified')->count(),
            'total_amount' => Transaction::where('merchant_id', $merchantId)->where('status', 'verified')->sum('amount'),
            'pending'      => Transaction::where('merchant_id', $merchantId)->where('status', 'pending')->count(),
            'suspicious'   => Transaction::where('merchant_id', $merchantId)->where('status', 'suspicious')->count(),
        ];

        return response()->json(['stats' => $stats]);
    }

    public function verify(string $uuid, Request $request): JsonResponse
    {
        $transaction = Transaction::where('uuid', $uuid)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        if ($transaction->isVerified()) {
            return response()->json(['message' => 'Transaction already verified.'], 422);
        }

        $transaction->update([
            'status'      => 'verified',
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        return response()->json([
            'message'     => 'Transaction verified successfully.',
            'transaction' => $transaction->fresh(),
        ]);
    }

    private function getMerchantId(Request $request): int
    {
        return $request->user()->ownedMerchant?->id
            ?? $request->user()->merchant_id;
    }
}
