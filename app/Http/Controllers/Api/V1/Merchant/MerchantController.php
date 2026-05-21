<?php

namespace App\Http\Controllers\Api\V1\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $merchant = $request->user()
            ->ownedMerchant
            ->load(['owner', 'bankAccounts'])
            ->loadCount(['employees', 'transactions']);

        return response()->json(['merchant' => $merchant]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'business_name' => 'sometimes|string|min:2|max:200',
            'business_type' => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:300',
            'city'          => 'nullable|string|max:100',
            'region'        => 'nullable|string|max:100',
            'tin_number'    => 'nullable|string|max:50',
        ]);

        $merchant = $request->user()->ownedMerchant;
        $merchant->update($request->only([
            'business_name', 'business_type',
            'address', 'city', 'region', 'tin_number',
        ]));

        return response()->json([
            'message'  => 'Profile updated.',
            'merchant' => $merchant->fresh(),
        ]);
    }

    public function epvsIdCard(Request $request): JsonResponse
    {
        $merchant = $request->user()->ownedMerchant;

        return response()->json([
            'epvs_id'       => $merchant->epvs_id,
            'business_name' => $merchant->business_name,
            'city'          => $merchant->city,
            'is_verified'   => $merchant->is_verified,
        ]);
    }
}
