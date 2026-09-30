<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seat_id',
        'booking_date',
        'slot',
        'status',
    ];

    // Fixed slots list defined in one place
    public static $slots = [
        '06:00-09:00',
        '09:00-12:00',
        '12:00-15:00',
        '15:00-18:00',
        '18:00-21:00',
    ];

    // Booking belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Booking belongs to a Seat
    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}
