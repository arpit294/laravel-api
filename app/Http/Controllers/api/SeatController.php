<?php

namespace App\Http\Controllers\api;

use App\Helpers\reply;
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
        return reply::successWith(Seat::all(), 'Seats fetched successfully');
    }

    // 2. Create a new seat
    public function store(SeatRequest $request)
    {
        try {

            $seat = new Seat;
            $seat->seat_number = strtoupper($request->seat_number);
            $seat->status = 'active';
            $seat->save();

            return reply::successWith(new SeatResource($seat), 'Seat Created Successfully');
        } catch (Exception $e) {
            return reply::errorWith(['error' => $e->getMessage()], 'Failed to create seat.');
        }

    }

    // 3. View a single seat
    public function show($id)
    {
        $seat = Seat::find($id);

        if (! $seat) {
            return reply::errorWith(null, 'Seat not found');
        }

        return reply::successWith(new SeatResource($seat), 'Seat fetched successfully');
    }

    // 4. Activate / Deactivate a seat
    public function toggleStatus($id)
    {
        try {
            $seat = Seat::find($id);

            if (! $seat) {
                return reply::errorWith(null, 'Seat not found');
            }

            $seat->status = ($seat->status === 'active') ? 'inactive' : 'active';
            $seat->save();

            return reply::successWith(new SeatResource($seat), "Seat status updated to {$seat->status}");
        } catch (Exception $e) {
            return reply::errorWith(['error' => $e->getMessage()], 'Failed to update seat.');
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

        return reply::successWith([
            'date' => $date,
            'slot' => $slot,
            'total_seats' => $totalSeats,
            'booked_seats' => $bookedSeatIds->count(),
            'available_seats' => $availableSeatsList->count(),
            'available_seat_list' => $availableSeatsList,
        ], 'Seat availability fetched successfully');
    }
}