<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'property_id',
        'unit_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'paid_to',
        'receipt',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public static function categories(): array
    {
        return [
            'Mantenimiento',
            'Servicios (luz, agua, gas)',
            'Limpieza',
            'Reparaciones mayores',
            'Administrativo',
            'Emergencias',
            'Pintura',
            'Plomería',
            'Electricidad',
            'Otros',
        ];
    }
}
