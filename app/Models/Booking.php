<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * AUTO-GENERATE BOOKING REFERENCE
     * This runs automatically before a booking is created in the database.
     */
    protected static function booted()
    {
        static::creating(function ($booking) {
            // Check if reference is empty to avoid overwriting if manually set
            if (empty($booking->booking_reference)) {
                // Generates a unique code like "BTN-A1B2C3"
                // md5(uniqid()) makes a random string, substr takes first 6 chars, strtoupper makes it caps.
                $booking->booking_reference = 'BTN-' . strtoupper(substr(md5(uniqid()), 0, 6));
            }
        });
    }

    public function resort(): BelongsTo
    {
        return $this->belongsTo(Resort::class);
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }
}