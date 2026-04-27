<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'property_id',
        'beneficiary_id',
        'code',
        'floor',
        'area_m2',
        'monthly_rent',
        'maintenance_amount',
        'currency',
        'status',
        'notes',
        'photo',
    ];

    protected $casts = [
        'area_m2' => 'decimal:2',
        'monthly_rent' => 'decimal:2',
        'maintenance_amount' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }
}
