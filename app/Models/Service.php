<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'service_category_id',
        'price',
        'description',
        'duration_minutes',
        'is_active',
        'images',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
        'images' => 'array',
    ];

    /**
     * Get the category that owns the service.
     */
    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    /**
     * Get the bookings for the service.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include category information.
     */
    public function scopeWithCategory($query)
    {
        return $query->with('serviceCategory');
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute()
    {
        return '৳' . number_format($this->price, 2);
    }
}
