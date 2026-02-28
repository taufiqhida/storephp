<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable implements FilamentUser
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin())
            return true;
        return in_array($permission, $this->permissions ?? []);
    }

    public function canManageProducts(): bool
    {
        return $this->hasPermission('manage_products');
    }
    public function canManageOrders(): bool
    {
        return $this->hasPermission('manage_orders');
    }
    public function canManagePayments(): bool
    {
        return $this->hasPermission('manage_payments');
    }
    public function canManageDiscounts(): bool
    {
        return $this->hasPermission('manage_discounts');
    }
    public function canManageTestimonials(): bool
    {
        return $this->hasPermission('manage_testimonials');
    }
    public function canManageFlashSales(): bool
    {
        return $this->hasPermission('manage_flash_sales');
    }
    public function canManageArticles(): bool
    {
        return $this->hasPermission('manage_articles');
    }
    public function canManageAdmins(): bool
    {
        return $this->hasPermission('manage_admins');
    }
    public function canManageSettings(): bool
    {
        return $this->hasPermission('manage_settings');
    }
}
