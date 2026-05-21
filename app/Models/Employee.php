<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'merchant_id', 'user_id', 'employee_code', 'position',
        'permissions', 'is_active', 'invited_by',
        'invited_at', 'accepted_at', 'invite_status',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active'   => 'boolean',
        'invited_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public const DEFAULT_PERMISSIONS = [
        'can_view_transactions'  => true,
        'can_verify_transaction' => false,
        'can_view_bank_accounts' => false,
        'can_manage_employees'   => false,
        'can_view_reports'       => true,
        'can_export_data'        => false,
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

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hasPermission(string $key): bool
    {
        $perms = $this->permissions ?? self::DEFAULT_PERMISSIONS;
        return (bool) ($perms[$key] ?? false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
