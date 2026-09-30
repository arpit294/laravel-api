<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\Student;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    // 1. List all bookings
    public function index(BookingRequest $request)
    {
        // 1. Start query on Booking model with student and seat relations
        $query = Booking::with(['student', 'seat']);

        // 2. Filter by date
        if ($request->has('date')) {
            $query->where('booking_date', $request->date);
        }

        // 3. Filter by slot
        if ($request->has('slot')) {
            $query->where('slot', $request->slot);
        }

        // 5. Get paginated results from $query
        $bookings = $query->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'bookings' => $bookings,
        ]);
    }

    public function store(BookingRequest $request)
    {
        try {

            // 1. Date cannot be in the past
            if (Carbon::parse($request->booking_date)->isPast() && ! Carbon::parse($request->booking_date)->isToday()) {
                return response()->json(['status' => 'error',
                    'message' => 'Cannot book for past dates']);
            }

            // 2. Student exists and is active
            $student = Student::find($request->student_id);
            if (! $student || ! $student->status) {
                return response()->json(['status' => 'error',
                    'message' => 'Student does not exist or is inactive']);
            }

            // 3. Seat exists and is active
            $seat = Seat::find($request->seat_id);
            if (! $seat || $seat->status !== 'active') {
                return response()->json(['status' => 'error',
                    'message' => 'Seat does not exist or is inactive']);
            }

            // 4. Check if seat is already booked for this date and slot
            $seatBooked = Booking::where('seat_id', $request->seat_id)
                ->where('booking_date', $request->booking_date)
                ->where('slot', $request->slot)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($seatBooked) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Seat is already booked for this date and slot',
                ], 409);
            }

            // 5. Check if student already has a booking for this date and slot
            $studentBooked = Booking::where('student_id', $request->student_id)
                ->where('booking_date', $request->booking_date)
                ->where('slot', $request->slot)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($studentBooked) {
                return response()->json(['status' => 'error',
                    'message' => 'Student already has a booking for this date and slot']);
            }

            return DB::transaction(function () use ($request) {
                $booking = new Booking;
                $booking->student_id = $request->student_id;
                $booking->seat_id = $request->seat_id;
                $booking->booking_date = $request->booking_date;
                $booking->slot = $request->slot;
                $booking->status = 'confirmed';
                $booking->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Seat Booked Successfully',
                    'data' => $booking,
                ]);
            });
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store booking.',
                'data' => null,
            ]);
        }
    }

    // Cancel Booking (do not delete, update status)
    public function cancel($id)
    {
        try {
            $booking = Booking::find($id);

            if (! $booking) {
                return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
            }

            $booking->status = 'cancelled';
            $booking->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully',
                'data' => $booking,
            ]);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancle booking.',
                'data' => null,
            ]);
        }
    }
}
