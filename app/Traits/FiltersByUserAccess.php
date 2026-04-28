<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Provides methods for filtering queries based on user property access.
 *
 * Use in controllers to restrict data to only the properties the
 * authenticated user is allowed to see.
 */
trait FiltersByUserAccess
{
    /**
     * Apply property restriction to a query that has a direct 'property_id' column.
     * Used for: Unit, Expense queries.
     */
    protected function applyPropertyFilter(Builder $query, string $column = 'property_id'): Builder
    {
        $propertyIds = auth()->user()->allowedPropertyIds();

        if ($propertyIds !== null) {
            $query->whereIn($column, $propertyIds);
        }

        return $query;
    }

    /**
     * Apply property restriction to a Property query (filter by 'id').
     */
    protected function applyPropertyIdFilter(Builder $query): Builder
    {
        $propertyIds = auth()->user()->allowedPropertyIds();

        if ($propertyIds !== null) {
            $query->whereIn('id', $propertyIds);
        }

        return $query;
    }

    /**
     * Apply property restriction to a Lease query via the unit relationship.
     */
    protected function applyLeasePropertyFilter(Builder $query): Builder
    {
        $propertyIds = auth()->user()->allowedPropertyIds();

        if ($propertyIds !== null) {
            $query->whereHas('unit', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        return $query;
    }

    /**
     * Apply property restriction to a Payment query via lease → unit relationship.
     */
    protected function applyPaymentPropertyFilter(Builder $query): Builder
    {
        $propertyIds = auth()->user()->allowedPropertyIds();

        if ($propertyIds !== null) {
            $query->whereHas('lease.unit', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        return $query;
    }

    /**
     * Apply property restriction to a Tenant query via leases → unit relationship.
     * Only shows tenants that have at least one lease in an allowed property.
     */
    protected function applyTenantPropertyFilter(Builder $query): Builder
    {
        $propertyIds = auth()->user()->allowedPropertyIds();

        if ($propertyIds !== null) {
            $query->whereHas('leases.unit', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        return $query;
    }

    /**
     * Check if current user can access a specific property.
     * Aborts with 403 if not.
     */
    protected function authorizeProperty(int $propertyId): void
    {
        if (! auth()->user()->canAccessProperty($propertyId)) {
            abort(403, 'No tienes acceso a esta propiedad.');
        }
    }

    /**
     * Check if current user has a specific permission.
     * Aborts with 403 if not.
     */
    protected function authorizePermission(string $permission): void
    {
        if (! auth()->user()->hasPermission($permission)) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }
    }
}
