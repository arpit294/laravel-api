<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Seat;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'sometimes|date_format:Y-m-d',
        ]);

        $date = $request->input('date', now()->toDateString());
        $activeSeats = Seat::where('status', 'active')->count();

        $bookedSeatsBySlot = Booking::where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('slot, COUNT(DISTINCT seat_id) as booked_seats')
            ->groupBy('slot')
            ->pluck('booked_seats', 'slot');

        $slotAvailability = collect(Booking::$slots)
            ->map(function ($slot) use ($activeSeats, $bookedSeatsBySlot) {
                $bookedSeats = (int) ($bookedSeatsBySlot[$slot] ?? 0);

                return [
                    'slot' => $slot,
                    'total_seats' => $activeSeats,
                    'booked_seats' => $bookedSeats,
                    'available_seats' => max(0, $activeSeats - $bookedSeats),
                ];
            })
            ->values();

        $availableAllDay = Seat::where('status', 'active')
            ->whereDoesntHave('bookings', function ($query) use ($date) {
                $query->where('booking_date', $date)
                    ->where('status', '!=', 'cancelled');
            })
            ->count();

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'total_seats' => Seat::count(),
            'total_bookings' => Booking::count(),
            'available_seats' => $availableAllDay,
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'slot_wise_availability' => $slotAvailability,
        ]);
    }
}
