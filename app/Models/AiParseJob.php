<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AiParseJob extends Model
{
    protected $fillable = [
        'sms_log_id', 'transaction_id', 'input_text',
        'parsed_result', 'confidence_score', 'model_used',
        'processing_time_ms', 'status', 'error_message',
    ];

    protected $casts = [
        'parsed_result' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) $model->uuid = (string) Str::uuid();
        });
    }

    public function smsLog() { return $this->belongsTo(SmsLog::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
}
