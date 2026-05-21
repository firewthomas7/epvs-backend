<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'              => 'EPVS Admin',
            'email'             => 'admin@epvs.et',
            'phone'             => '+251911000000',
            'password'          => Hash::make('Admin@1234!'),
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // Merchant Owner
        $owner = User::create([
            'name'              => 'Abebe Kebede',
            'email'             => 'abebe@selammarket.et',
            'phone'             => '+251912345678',
            'password'          => Hash::make('Demo@1234!'),
            'role'              => 'merchant',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // Merchant
        $merchant = Merchant::create([
            'epvs_id'             => 'EPVS-ET-00001',
            'business_name'       => 'Selam Mini Market',
            'business_type'       => 'Retail',
            'owner_id'            => $owner->id,
            'city'                => 'Addis Ababa',
            'region'              => 'Addis Ababa',
            'address'             => 'Bole Road, Near Airport',
            'tin_number'          => 'TIN-123456789',
            'is_verified'         => true,
            'verification_status' => 'verified',
        ]);

        $owner->update(['merchant_id' => $merchant->id]);

        // Bank Account
        $bankAccount = BankAccount::create([
            'merchant_id'              => $merchant->id,
            'bank_name'                => 'Commercial Bank of Ethiopia',
            'bank_code'                => 'CBE',
            'account_number_encrypted' => Crypt::encryptString('1000234567890'),
            'account_number_masked'    => '*********7890',
            'account_name'             => 'Selam Mini Market',
            'account_type'             => 'current',
            'is_primary'               => true,
            'is_active'                => true,
        ]);

        // Telebirr Account
        $telebirrAccount = BankAccount::create([
            'merchant_id'              => $merchant->id,
            'bank_name'                => 'Telebirr',
            'bank_code'                => 'TELEBIRR',
            'account_number_encrypted' => Crypt::encryptString('0912345678'),
            'account_number_masked'    => '******5678',
            'account_name'             => 'Selam Mini Market',
            'account_type'             => 'current',
            'is_primary'               => false,
            'is_active'                => true,
        ]);

        // Employee (Cashier)
        $cashierUser = User::create([
            'name'              => 'Almaz Tadesse',
            'email'             => 'almaz@selammarket.et',
            'phone'             => '+251923456789',
            'password'          => Hash::make('Cashier@1234!'),
            'role'              => 'employee',
            'merchant_id'       => $merchant->id,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        Employee::create([
            'merchant_id'   => $merchant->id,
            'user_id'       => $cashierUser->id,
            'position'      => 'Cashier',
            'permissions'   => [
                'can_view_transactions'  => true,
                'can_verify_transaction' => true,
                'can_view_bank_accounts' => false,
                'can_manage_employees'   => false,
                'can_view_reports'       => true,
                'can_export_data'        => false,
            ],
            'is_active'     => true,
            'invite_status' => 'accepted',
            'accepted_at'   => now(),
        ]);

        // 50 Demo Transactions
        $senders = [
            ['name' => 'Tigist Alemu',    'phone' => '0923456789'],
            ['name' => 'Mohammed Ahmed',  'phone' => '0934567890'],
            ['name' => 'Sara Hailu',      'phone' => '0945678901'],
            ['name' => 'Yonas Tesfaye',   'phone' => '0956789012'],
            ['name' => 'Hana Girma',      'phone' => '0967890123'],
            ['name' => 'Dawit Bekele',    'phone' => '0978901234'],
            ['name' => 'Meron Tadesse',   'phone' => '0989012345'],
            ['name' => 'Kaleb Assefa',    'phone' => '0990123456'],
            ['name' => 'Liya Solomon',    'phone' => '0911234567'],
            ['name' => 'Biruk Worku',     'phone' => '0922345678'],
        ];

        $sources = ['bank_api', 'bank_api', 'bank_api', 'telebirr', 'sms'];
        $statuses = ['verified', 'verified', 'verified', 'verified', 'pending', 'suspicious'];
        $amounts = [50, 100, 150, 200, 250, 300, 500, 750, 1000, 1500, 2000, 2500, 3000, 5000];

        for ($i = 0; $i < 50; $i++) {
            $sender    = $senders[array_rand($senders)];
            $source    = $sources[array_rand($sources)];
            $status    = $statuses[array_rand($statuses)];
            $amount    = $amounts[array_rand($amounts)];
            $bankAcct  = $source === 'telebirr' ? $telebirrAccount : $bankAccount;
            $daysAgo   = rand(0, 30);
            $hoursAgo  = rand(0, 23);

            Transaction::create([
                'merchant_id'         => $merchant->id,
                'bank_account_id'     => $bankAcct->id,
                'source_type'         => $source,
                'status'              => $status,
                'amount'              => $amount + (rand(0, 99) / 100),
                'currency'            => 'ETB',
                'sender_name'         => $sender['name'],
                'sender_phone'        => $sender['phone'],
                'bank_name'           => $bankAcct->bank_name,
                'bank_code'           => $bankAcct->bank_code,
                'bank_reference'      => strtoupper($bankAcct->bank_code) . '-TXN-' . uniqid(),
                'ai_confidence_score' => rand(75, 99),
                'verified_at'         => $status === 'verified' ? now()->subDays($daysAgo)->subHours($hoursAgo) : null,
                'created_at'          => now()->subDays($daysAgo)->subHours($hoursAgo),
                'updated_at'          => now()->subDays($daysAgo)->subHours($hoursAgo),
            ]);
        }
    }
}
