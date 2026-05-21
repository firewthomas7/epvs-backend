<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'epvs_ref', 'merchant_id', 'bank_account_id',
        'source_type', 'status', 'amount', 'currency',
        'sender_name', 'sender_phone', 'sender_account',
        'bank_name', 'bank_code', 'bank_reference',
        'description', 'raw_payload', 'ai_confidence_score',
        'bank_timestamp', 'verified_at', 'verified_by', 'is_announced',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'raw_payload'    => 'array',
        'is_announced'   => 'boolean',
        'bank_timestamp' => 'datetime',
        'verified_at'    => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->epvs_ref)) {
                $model->epvs_ref = self::generateEpvsRef();
            }
        });
    }

    public static function generateEpvsRef(): string
    {
        do {
            $ref = 'EPVS-' . strtoupper(substr(uniqid(), -8)) . '-' . rand(100, 999);
        } while (self::where('epvs_ref', $ref)->exists());
        return $ref;
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function voiceAnnouncement()
    {
        return $this->hasOne(VoiceAnnouncement::class);
    }

    public function isVerified(): bool   { return $this->status === 'verified'; }
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isSuspicious(): bool { return $this->status === 'suspicious'; }

    public function scopeForMerchant($query, int $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
