<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VoiceAnnouncement extends Model
{
    protected $fillable = [
        'merchant_id', 'transaction_id', 'script',
        'language', 'audio_url', 'status',
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
