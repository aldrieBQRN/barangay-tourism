<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accommodation extends Model
{
    use HasFactory;

    /**
     * Allows all fields to be saved via Filament.
     */
    protected $guarded = [];

    /**
     * Get the resort that owns the accommodation.
     */
    public function resort(): BelongsTo
    {
        return $this->belongsTo(Resort::class);
    }
}