<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Default permissions per role.
     * Admin gets everything automatically (bypass).
     */
    public const ROLE_PERMISSIONS = [
        'manager' => [
            'properties.view', 'properties.create', 'properties.edit', 'properties.delete',
            'units.view', 'units.create', 'units.edit', 'units.delete',
            'tenants.view', 'tenants.create', 'tenants.edit', 'tenants.delete',
            'leases.view', 'leases.create', 'leases.edit', 'leases.delete',
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'reports.view', 'reports.export',
            'notifications.view', 'notifications.manage',
        ],
        'viewer' => [
            'properties.view',
            'units.view',
            'tenants.view',
            'leases.view',
            'payments.view',
            'expenses.view',
            'reports.view',
            'notifications.view',
        ],
    ];

    /**
     * All available permissions in the system (for the admin UI).
     */
    public const ALL_PERMISSIONS = [
        'properties'    => ['properties.view', 'properties.create', 'properties.edit', 'properties.delete'],
        'units'         => ['units.view', 'units.create', 'units.edit', 'units.delete'],
        'tenants'       => ['tenants.view', 'tenants.create', 'tenants.edit', 'tenants.delete'],
        'leases'        => ['leases.view', 'leases.create', 'leases.edit', 'leases.delete'],
        'payments'      => ['payments.view', 'payments.create', 'payments.edit', 'payments.delete'],
        'expenses'      => ['expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete'],
        'reports'       => ['reports.view', 'reports.export'],
        'users'         => ['users.view', 'users.create', 'users.edit', 'users.delete'],
        'notifications' => ['notifications.view', 'notifications.manage'],
    ];

    /**
     * Human-readable labels for permissions.
     */
    public const PERMISSION_LABELS = [
        'properties.view'      => 'Ver Propiedades',
        'properties.create'    => 'Crear Propiedades',
        'properties.edit'      => 'Editar Propiedades',
        'properties.delete'    => 'Eliminar Propiedades',
        'units.view'           => 'Ver Unidades',
        'units.create'         => 'Crear Unidades',
        'units.edit'           => 'Editar Unidades',
        'units.delete'         => 'Eliminar Unidades',
        'tenants.view'         => 'Ver Inquilinos',
        'tenants.create'       => 'Crear Inquilinos',
        'tenants.edit'         => 'Editar Inquilinos',
        'tenants.delete'       => 'Eliminar Inquilinos',
        'leases.view'          => 'Ver Contratos',
        'leases.create'        => 'Crear Contratos',
        'leases.edit'          => 'Editar Contratos',
        'leases.delete'        => 'Eliminar Contratos',
        'payments.view'        => 'Ver Pagos',
        'payments.create'      => 'Crear Pagos',
        'payments.edit'        => 'Editar Pagos',
        'payments.delete'      => 'Eliminar Pagos',
        'expenses.view'        => 'Ver Gastos',
        'expenses.create'      => 'Crear Gastos',
        'expenses.edit'        => 'Editar Gastos',
        'expenses.delete'      => 'Eliminar Gastos',
        'reports.view'         => 'Ver Reportes',
        'reports.export'       => 'Exportar Reportes',
        'users.view'           => 'Ver Usuarios',
        'users.create'         => 'Crear Usuarios',
        'users.edit'           => 'Editar Usuarios',
        'users.delete'         => 'Eliminar Usuarios',
        'notifications.view'   => 'Ver Notificaciones',
        'notifications.manage' => 'Gestionar Notificaciones',
    ];

    public const MODULE_LABELS = [
        'properties'    => 'Propiedades',
        'units'         => 'Unidades / Locales',
        'tenants'       => 'Inquilinos',
        'leases'        => 'Contratos',
        'payments'      => 'Pagos',
        'expenses'      => 'Gastos',
        'reports'       => 'Reportes',
        'users'         => 'Usuarios',
        'notifications' => 'Notificaciones',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────

    /**
     * Explicit permissions assigned to this user (overrides role defaults).
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Properties this user is restricted to.
     * If empty → user can see all properties (subject to role permissions).
     */
    public function allowedProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'user_property')->withTimestamps();
    }

    // ─── Role Helpers ───────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    // ─── Permission Helpers ─────────────────────────────────────

    /**
     * Check if user has a specific permission.
     *
     * Priority:
     * 1. Admin → always true
     * 2. Explicit user_permissions → if any exist for this user, use them (full override)
     * 3. Fall back to role defaults
     */
    public function hasPermission(string $permission): bool
    {
        // Admin bypasses everything
        if ($this->isAdmin()) {
            return true;
        }

        // Check explicit permissions first
        $explicitPermissions = $this->userPermissions->pluck('permission')->toArray();

        if (! empty($explicitPermissions)) {
            return in_array($permission, $explicitPermissions, true);
        }

        // Fall back to role defaults
        $rolePermissions = self::ROLE_PERMISSIONS[$this->role] ?? [];

        return in_array($permission, $rolePermissions, true);
    }

    /**
     * Check if user can access a specific property.
     *
     * - Admin → always true
     * - No property restrictions → true (can see all)
     * - Has restrictions → only allowed properties
     */
    public function canAccessProperty(int $propertyId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $allowed = $this->allowedProperties;

        // No restrictions → can see all
        if ($allowed->isEmpty()) {
            return true;
        }

        return $allowed->contains('id', $propertyId);
    }

    /**
     * Get array of allowed property IDs, or null if no restriction.
     */
    public function allowedPropertyIds(): ?array
    {
        if ($this->isAdmin()) {
            return null;
        }

        $ids = $this->allowedProperties->pluck('id')->toArray();

        return empty($ids) ? null : $ids;
    }

    /**
     * Get all effective permissions for this user.
     */
    public function effectivePermissions(): array
    {
        if ($this->isAdmin()) {
            // Flatten all permissions
            $all = [];
            foreach (self::ALL_PERMISSIONS as $perms) {
                $all = array_merge($all, $perms);
            }
            return $all;
        }

        $explicit = $this->userPermissions->pluck('permission')->toArray();

        if (! empty($explicit)) {
            return $explicit;
        }

        return self::ROLE_PERMISSIONS[$this->role] ?? [];
    }
}
