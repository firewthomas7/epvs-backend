<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SmsLog extends Model
{
    protected $fillable = [
        'merchant_id', 'from_number', 'to_number', 'body',
        'received_at', 'parsed', 'transaction_id',
        'parsing_status', 'parsing_error',
    ];

    protected $casts = [
        'parsed'      => 'boolean',
        'received_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) $model->uuid = (string) Str::uuid();
        });
    }

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
}
