<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'          => 'required|string|min:2|max:150',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'required|string|unique:users,phone',
            'password'      => 'required|string|min:8|confirmed',
            'business_name' => 'required|string|min:2|max:200',
            'business_type' => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'region'        => 'nullable|string|max:100',
        ]);

        $result = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => $request->password,
                'role'     => 'merchant',
            ]);

            $epvsId = $this->generateEpvsId();

            $merchant = Merchant::create([
                'epvs_id'             => $epvsId,
                'business_name'       => $request->business_name,
                'business_type'       => $request->business_type,
                'owner_id'            => $user->id,
                'city'                => $request->city,
                'region'              => $request->region,
                'is_verified'         => true,
                'verification_status' => 'verified',
            ]);

            $user->update(['merchant_id' => $merchant->id]);

            $token = $user->createToken('epvs-app')->plainTextToken;

            return compact('user', 'merchant', 'token');
        });

        return response()->json([
            'message'  => 'Merchant registered successfully.',
            'token'    => $result['token'],
            'user'     => $result['user'],
            'merchant' => $result['merchant'],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->orWhere('phone', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        $token = $user->createToken('epvs-app')->plainTextToken;

        return response()->json([
            'message'    => 'Login successful.',
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'uuid'        => $user->uuid,
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'role'        => $user->role,
                'merchant'    => $user->ownedMerchant ?? $user->merchant,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['ownedMerchant', 'merchant', 'employee']);
        return response()->json(['user' => $user]);
    }

    private function generateEpvsId(): string
    {
        do {
            $id = 'EPVS-ET-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Merchant::where('epvs_id', $id)->exists());
        return $id;
    }
}
