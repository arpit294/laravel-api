<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Seat;
use Exception;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    // 1. List all seats
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'seats' => Seat::all(),
        ]);
    }

    // 2. Create a new seat
    public function store(Request $request)
    {
        try {
            $request->validate([
                'seat_number' => 'required|string|unique:seats,seat_number',
            ]);

            $seat = new Seat;
            $seat->seat_number = strtoupper($request->seat_number);
            $seat->status = 'active';
            $seat->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Seat Created Successfully',
                'data' => $seat,
            ]);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create seat.',
                'data' => null,
            ]);

        }

    }

    // 3. View a single seat
    public function show($id)
    {
        $seat = Seat::find($id);

        if (! $seat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seat not found',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $seat,
        ]);
    }

    // 4. Activate / Deactivate a seat
    public function toggleStatus($id)
    {
        try{
        $seat = Seat::find($id);

        if (! $seat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seat not found',
            ], 404);
        }

        $seat->status = ($seat->status === 'active') ? 'inactive' : 'active';
        $seat->save();

        return response()->json([
            'status' => 'success',
            'message' => "Seat status updated to {$seat->status}",
            'data' => $seat,
        ]);
        } catch(Exception){
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update seat.',
                'data' => null,
            ]);
        }
    }

    // 5. Seat Availability API
    public function availableSeats(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'slot' => 'required|in:06:00-09:00,09:00-12:00,12:00-15:00,15:00-18:00,18:00-21:00',
        ]);

        $date = $request->date;
        $slot = $request->slot;

        // Find seat IDs that have an active booking ('status' = 'yes') for this date and slot
        $bookedSeatIds = Booking::where('booking_date', $date)
            ->where('slot', $slot)
            ->where('status', 'yes')
            ->pluck('seat_id');

        // Total active seats in library
        $totalSeats = Seat::where('status', 'active')->count();

        // Seats that are active and not in the booked list
        $availableSeatsList = Seat::where('status', 'active')
            ->whereNotIn('id', $bookedSeatIds)
            ->get();

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'slot' => $slot,
            'total_seats' => $totalSeats,
            'booked_seats' => $bookedSeatIds->count(),
            'available_seats' => $availableSeatsList->count(),
            'available_seat_list' => $availableSeatsList,
        ]);
    }
}
