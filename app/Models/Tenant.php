<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'full_name',
        'document_id',
        'phone',
        'email',
        'address',
        'notes',
        'photo',
    ];

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }
}
