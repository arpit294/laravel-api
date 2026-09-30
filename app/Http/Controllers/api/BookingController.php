<?php

namespace App\Http\Controllers\api;

use App\Helpers\reply;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Resources\BookingResource;
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

        return reply::successWith(new BookingResource($bookings), 'Bookings fetched successfully');
    }

    public function store(BookingRequest $request)
    {
        try {

            // 1. Date cannot be in the past
            if (Carbon::parse($request->booking_date)->isPast() && ! Carbon::parse($request->booking_date)->isToday()) {
                return reply::errorWith(null, 'Cannot book for past dates');
            }

            // 2. Student exists and is active
            $student = Student::find($request->student_id);
            if (! $student || ! $student->status) {
                return reply::errorWith(null, 'Student does not exist or is inactive');
            }

            // 3. Seat exists and is active
            $seat = Seat::find($request->seat_id);
            if (! $seat || $seat->status !== 'active') {
                return reply::errorWith(null, 'Seat does not exist or is inactive');
            }

            // 4. Check if seat is already booked for this date and slot
            $seatBooked = Booking::where('seat_id', $request->seat_id)
                ->where('booking_date', $request->booking_date)
                ->where('slot', $request->slot)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($seatBooked) {
                return reply::errorWith(null, 'Seat is already booked for this date and slot');
            }

            // 5. Check if student already has a booking for this date and slot
            $studentBooked = Booking::where('student_id', $request->student_id)
                ->where('booking_date', $request->booking_date)
                ->where('slot', $request->slot)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($studentBooked) {
                return reply::errorWith(null, 'Student already has a booking for this date and slot');
            }

            return DB::transaction(function () use ($request) {
                $booking = new Booking;
                $booking->student_id = $request->student_id;
                $booking->seat_id = $request->seat_id;
                $booking->booking_date = $request->booking_date;
                $booking->slot = $request->slot;
                $booking->status = 'confirmed';
                $booking->save();

                return reply::successWith(new BookingResource($booking), 'Seat Booked Successfully');
            });
        } catch (Exception $e) {
            return reply::errorWith(['error' => $e->getMessage()], 'Failed to store booking.');
        }
    }

    // Cancel Booking (do not delete, update status)
    public function cancel($id)
    {
        try {
            $booking = Booking::find($id);

            if (! $booking) {
                return reply::errorWith(null, 'Booking not found');
            }

            $booking->status = 'cancelled';
            $booking->save();

            return reply::successWith(new BookingResource($booking), 'Booking cancelled successfully');
        } catch (Exception $e) {
            return reply::errorWith(['error' => $e->getMessage()], 'Failed to cancle booking.');
        }
    }
}