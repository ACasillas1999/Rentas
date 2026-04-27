<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    protected $fillable = [
        'unit_id',
        'tenant_id',
        'contract_number',
        'start_date',
        'end_date',
        'first_period_start',
        'monthly_amount',
        'maintenance_amount',
        'deposit_amount',
        'status',
        'notes',
        'contract_pdf',
        'previous_lease_id',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'first_period_start' => 'date',
        'monthly_amount'     => 'decimal:2',
        'maintenance_amount' => 'decimal:2',
        'deposit_amount'     => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(LeaseNotification::class);
    }

    public function previousLease(): BelongsTo
    {
        return $this->belongsTo(Lease::class, 'previous_lease_id');
    }

    public function nextLease(): HasOne
    {
        return $this->hasOne(Lease::class, 'previous_lease_id');
    }
}
