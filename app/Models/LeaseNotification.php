<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseNotification extends Model
{
    protected $fillable = [
        'lease_id',
        'email',
        'notify_30_days',
        'notify_15_days',
        'notify_end_date',
        'sent_30_days_at',
        'sent_15_days_at',
        'sent_end_date_at',
    ];

    protected $casts = [
        'notify_30_days' => 'boolean',
        'notify_15_days' => 'boolean',
        'notify_end_date' => 'boolean',
        'sent_30_days_at' => 'datetime',
        'sent_15_days_at' => 'datetime',
        'sent_end_date_at' => 'datetime',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }
}
