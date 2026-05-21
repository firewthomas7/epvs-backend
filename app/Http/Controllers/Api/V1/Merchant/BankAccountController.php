<?php

namespace App\Http\Controllers\Api\V1\Merchant;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class BankAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $merchant = $request->user()->ownedMerchant;
        $accounts = BankAccount::where('merchant_id', $merchant->id)->get();

        return response()->json(['bank_accounts' => $accounts]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'bank_code'      => 'required|string',
            'account_number' => 'required|string|min:6|max:30',
            'account_name'   => 'required|string|min:2|max:150',
            'account_type'   => 'nullable|in:savings,current,business',
            'is_primary'     => 'nullable|boolean',
        ]);

        $merchant = $request->user()->ownedMerchant;

        if ($merchant->bankAccounts()->count() >= 5) {
            return response()->json(['message' => 'Maximum 5 bank accounts allowed.'], 422);
        }

        $bankNames = [
            'CBE'       => 'Commercial Bank of Ethiopia',
            'AWASH'     => 'Awash Bank',
            'DASHEN'    => 'Dashen Bank',
            'TELEBIRR'  => 'Telebirr',
            'ABYSSINIA' => 'Bank of Abyssinia',
            'WEGAGEN'   => 'Wegagen Bank',
            'NIB'       => 'Nib International Bank',
        ];

        $bankCode = strtoupper($request->bank_code);

        if ($request->boolean('is_primary')) {
            BankAccount::where('merchant_id', $merchant->id)->update(['is_primary' => false]);
        }

        $account = BankAccount::create([
            'merchant_id'               => $merchant->id,
            'bank_name'                 => $bankNames[$bankCode] ?? $bankCode,
            'bank_code'                 => $bankCode,
            'account_number_encrypted'  => Crypt::encryptString($request->account_number),
            'account_number_masked'     => BankAccount::maskAccountNumber($request->account_number),
            'account_name'              => $request->account_name,
            'account_type'              => $request->account_type ?? 'current',
            'is_primary'                => $request->boolean('is_primary', $merchant->bankAccounts()->count() === 0),
            'is_active'                 => true,
        ]);

        return response()->json([
            'message'      => 'Bank account linked successfully.',
            'bank_account' => $account,
            'webhook_url'  => url("/api/v1/webhooks/bank/{$account->webhook_token}"),
        ], 201);
    }

    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $account  = BankAccount::where('uuid', $uuid)->firstOrFail();
        $merchant = $request->user()->ownedMerchant;

        if ($account->merchant_id !== $merchant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $account->update(['is_active' => false]);

        return response()->json(['message' => 'Bank account unlinked successfully.']);
    }
}
