<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebhookConfig extends Model
{
    protected $fillable = [
        'merchant_id', 'bank_account_id', 'bank_name', 'bank_code',
        'secret_key_hash', 'allowed_ips', 'is_active',
        'last_ping_at', 'total_calls',
    ];

    protected $hidden = ['secret_key_hash'];

    protected $casts = [
        'allowed_ips'  => 'array',
        'is_active'    => 'boolean',
        'last_ping_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) $model->uuid = (string) Str::uuid();
        });
    }

    public function merchant() { return $this->belongsTo(Merchant::class); }
}
