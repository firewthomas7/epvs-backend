<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'epvs_id', 'business_name', 'business_type', 'owner_id',
        'address', 'city', 'region', 'tin_number', 'business_license',
        'is_verified', 'verification_status', 'rejection_reason', 'logo_url',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isVerified(): bool
    {
        return $this->is_verified && $this->verification_status === 'verified';
    }

    public function getPrimaryBankAccount(): ?BankAccount
    {
        return $this->bankAccounts()->where('is_primary', true)->where('is_active', true)->first()
            ?? $this->bankAccounts()->where('is_active', true)->first();
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true)->where('verification_status', 'verified');
    }
}
