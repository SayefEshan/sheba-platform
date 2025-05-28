<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active admins.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if admin is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if admin can perform action.
     */
    public function canPerformAction(string $action): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        // Define permissions for regular admin
        $permissions = [
            'view_bookings' => true,
            'update_booking_status' => true,
            'view_services' => true,
            'create_service' => false,
            'update_service' => false,
            'delete_service' => false,
            'manage_admins' => false,
        ];

        return $permissions[$action] ?? false;
    }
}
