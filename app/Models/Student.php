<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'status',
    ];

    // A student can have multiple bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
