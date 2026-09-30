<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeatRequest;
use App\Http\Resources\SeatResource;
use App\Models\Booking;
use App\Models\Seat;
use Exception;

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
    public function store(SeatRequest $request)
    {
        try {

            $seat = new Seat;
            $seat->seat_number = strtoupper($request->seat_number);
            $seat->status = 'active';
            $seat->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Seat Created Successfully',
                'data' => new SeatResource($seat),
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
            'data' => new SeatResource($seat),
        ]);
    }

    // 4. Activate / Deactivate a seat
    public function toggleStatus($id)
    {
        try {
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
                'data' => new SeatResource($seat),
            ]);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update seat.',
                'data' => null,
            ]);
        }
    }

    // 5. Seat Availability API
    public function availableSeats(SeatRequest $request)
    {
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