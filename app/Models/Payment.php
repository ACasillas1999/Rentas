<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const TAX_RATE = 0.16;

    protected $fillable = [
        'lease_id',
        'type',
        'period_label',
        'period_start',
        'period_end',
        'period_number',
        'total_periods',
        'due_date',
        'paid_at',
        'amount',
        'subtotal',
        'tax_amount',
        'paid_amount',
        'late_fee',
        'status',
        'payment_method',
        'reference',
        'notes',
        'receipt',
        'invoice_pdf',
        'invoice_xml',
        'invoice_folio',
        'invoiced_at',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'paid_at'      => 'date',
        'invoiced_at'  => 'date',
        'period_start' => 'date',
        'period_end'   => 'date',
        'amount'       => 'decimal:2',
        'subtotal'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'late_fee'     => 'decimal:2',
        'receipt'      => 'array',
        'invoice_pdf'  => 'array',
        'invoice_xml'  => 'array',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Payment $payment) {
            // Desglose automático de IVA (16%) si no se proporcionan subtotal o tax_amount
            // O si el monto total (amount) ha cambiado y necesitamos volver a calcular
            if (empty($payment->subtotal) || empty($payment->tax_amount) || $payment->isDirty('amount')) {
                $total = (float) $payment->amount;
                $payment->subtotal = $total / (1 + self::TAX_RATE);
                $payment->tax_amount = $total - $payment->subtotal;
            }
        });
    }
}
