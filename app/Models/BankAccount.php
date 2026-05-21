<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id', 'bank_name', 'bank_code',
        'account_number_encrypted', 'account_number_masked',
        'account_name', 'account_type', 'webhook_token',
        'is_active', 'is_primary', 'last_transaction_at',
    ];

    protected $hidden = ['account_number_encrypted', 'webhook_token'];

    protected $casts = [
        'is_active'           => 'boolean',
        'is_primary'          => 'boolean',
        'last_transaction_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->webhook_token)) {
                $model->webhook_token = bin2hex(random_bytes(32));
            }
        });
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public static function maskAccountNumber(string $number): string
    {
        $len = strlen($number);
        if ($len <= 4) return $number;
        return str_repeat('*', $len - 4) . substr($number, -4);
    }
}
