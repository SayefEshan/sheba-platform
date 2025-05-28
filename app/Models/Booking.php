<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Notifications\BookingConfirmed;
use Illuminate\Notifications\Notifiable;

class Booking extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'booking_id',
        'service_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'service_price',
        'status',
        'scheduled_at',
        'notes',
        'admin_notes',
        'confirmed_at',
        'completed_at',
    ];

    protected $casts = [
        'service_price' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_id)) {
                $booking->booking_id = static::generateBookingId();
            }
        });
    }

    /**
     * Generate a unique booking ID.
     */
    public static function generateBookingId(): string
    {
        do {
            $bookingId = 'SB' . strtoupper(Str::random(8));
        } while (static::where('booking_id', $bookingId)->exists());

        return $bookingId;
    }

    /**
     * Get the service that owns the booking.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope a query by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to include service information.
     */
    public function scopeWithService($query)
    {
        return $query->with(['service', 'service.serviceCategory']);
    }

    /**
     * Get formatted service price with currency.
     */
    public function getFormattedServicePriceAttribute()
    {
        return '৳' . number_format($this->service_price, 2);
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Check if booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
            (!$this->scheduled_at || $this->scheduled_at->isFuture());
    }

    /**
     * Confirm the booking.
     */
    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Find or create user for notification
        $user = \App\Models\User::firstOrCreate(
            ['email' => $this->customer_email],
            [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'address' => $this->customer_address,
            ]
        );

        // Send notification
        $user->notify(new BookingConfirmed($this));
    }

    /**
     * Complete the booking.
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
