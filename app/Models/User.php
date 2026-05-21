<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'role', 'merchant_id', 'is_active', 'fcm_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
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

    public function ownedMerchant()
    {
        return $this->hasOne(Merchant::class, 'owner_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isMerchant(): bool { return $this->role === 'merchant'; }
    public function isEmployee(): bool { return $this->role === 'employee'; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }

    public function hasPermission(string $permission): bool
    {
        if ($this->isMerchant() || $this->isAdmin()) return true;
        $emp = $this->employee;
        if (!$emp) return false;
        $perms = $emp->permissions ?? [];
        return (bool) ($perms[$permission] ?? false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
