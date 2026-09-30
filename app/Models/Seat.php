<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'seat_number',
        'status',
    ];

    // A seat can have multiple bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
