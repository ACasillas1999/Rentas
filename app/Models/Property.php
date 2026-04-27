<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'name',
        'type',
        'address',
        'city',
        'state',
        'notes',
        'latitude',
        'longitude',
        'photo',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
