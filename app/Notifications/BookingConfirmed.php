<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmed - ' . $this->booking->booking_id)
            ->greeting('Hello ' . $this->booking->customer_name . '!')
            ->line('Your booking has been confirmed.')
            ->line('Service: ' . $this->booking->service->name)
            ->line('Price: ' . $this->booking->formatted_service_price)
            ->line('Scheduled At: ' . ($this->booking->scheduled_at ? $this->booking->scheduled_at->format('F j, Y g:i A') : 'To be scheduled'))
            ->line('Thank you for choosing our service!')
            ->line('If you have any questions, please contact us.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->booking_id,
            'status' => 'confirmed',
            'service_name' => $this->booking->service->name,
            'scheduled_at' => $this->booking->scheduled_at,
        ];
    }
}
