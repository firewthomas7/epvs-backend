<?php

namespace App\Http\Controllers\Api\V1\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $merchant  = $request->user()->ownedMerchant;
        $employees = Employee::where('merchant_id', $merchant->id)
            ->with(['user'])
            ->get();

        return response()->json(['employees' => $employees]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'phone'    => 'required|string|unique:users,phone',
            'email'    => 'nullable|email|unique:users,email',
            'position' => 'nullable|string|max:100',
        ]);

        $merchant = $request->user()->ownedMerchant;

        $result = DB::transaction(function () use ($request, $merchant) {
            $password = Str::random(10);

            $user = User::create([
                'name'        => $request->name,
                'email'       => $request->email ?? $request->phone . '@epvs.local',
                'phone'       => $request->phone,
                'password'    => $password,
                'role'        => 'employee',
                'merchant_id' => $merchant->id,
            ]);

            $employee = Employee::create([
                'merchant_id'   => $merchant->id,
                'user_id'       => $user->id,
                'position'      => $request->position,
                'permissions'   => Employee::DEFAULT_PERMISSIONS,
                'invited_by'    => $request->user()->id,
                'invited_at'    => now(),
                'invite_status' => 'accepted',
                'accepted_at'   => now(),
                'is_active'     => true,
            ]);

            return compact('user', 'employee', 'password');
        });

        return response()->json([
            'message'      => 'Employee added successfully.',
            'employee'     => $result['employee']->load('user'),
            'temp_password'=> $result['password'],
        ], 201);
    }

    public function updatePermissions(string $uuid, Request $request): JsonResponse
    {
        $request->validate(['permissions' => 'required|array']);

        $employee = Employee::where('uuid', $uuid)->firstOrFail();
        $employee->update(['permissions' => $request->permissions]);

        return response()->json([
            'message'  => 'Permissions updated.',
            'employee' => $employee->fresh('user'),
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $employee = Employee::where('uuid', $uuid)->firstOrFail();
        $employee->update(['is_active' => false]);
        $employee->user->update(['is_active' => false]);
        $employee->delete();

        return response()->json(['message' => 'Employee removed successfully.']);
    }
}
