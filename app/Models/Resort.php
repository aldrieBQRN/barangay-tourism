<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resort extends Model
{
    use HasFactory;

    /**
     * Disable mass assignment protection.
     * This allows Filament to save all the form fields like rates and names.
     */
    protected $guarded = [];

    /**
     * A resort can have many accommodations (rooms, cottages, etc.).
     */
    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
    }

    /**
     * A resort can have many bookings.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}