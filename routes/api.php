<?php

use App\Http\Controllers\api\BookingController;
use App\Http\Controllers\api\DashboardController;
use App\Http\Controllers\api\SeatController;
use App\Http\Controllers\api\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Student Routes
    Route::apiResource('students', StudentController::class);

    Route::patch('students/{student}/deactivate', [StudentController::class, 'deactivate']);
    Route::patch('students/{student}/activate', [StudentController::class, 'activate']);
    Route::get('students/{student}/bookings', [StudentController::class, 'bookingHistory']);

    // Seat Routes
    Route::apiResource('seats', SeatController::class)
        ->only(['index', 'store', 'show']);

    Route::get('seats/available', [SeatController::class, 'availableSeats']);
    Route::patch('seats/{seat}/status', [SeatController::class, 'toggleStatus']);

    // Booking Routes
    Route::apiResource('bookings', BookingController::class)
        ->only(['index', 'store']);

    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Dashboard Route
    Route::get('library/dashboard', [DashboardController::class, 'index']);

});

// API detailes for the postMan
// 1	Register Student	    POST	http://127.0.0.1:8000/api/v1/students	{"name": "Arpit Vadhiyari", "mobile": "8200903907", "email": "arpit@gmail.com"}
// 2	List All Students	    GET	http://127.0.0.1:8000/api/v1/students	None
// 3	View Single Student	    GET	http://127.0.0.1:8000/api/v1/students/1	None
// 4	Update Student	PUT	    http://127.0.0.1:8000/api/v1/students/1	      {"name": "Arpit Updated", "mobile": "9876543210", "email": "john@gmail.com"}
// 5	Deactivate Student	    PATCH	http://127.0.0.1:8000/api/v1/students/1/deactivate	None
// 6	Student Booking History	GET	http://127.0.0.1:8000/api/v1/students/1/bookings	None

// 2. Seat Management URLs
// 7	Create Seat A01	POST	    http://127.0.0.1:8000/api/v1/seats	{"seat_number": "A01"}
// 9	List All Seats	GET	        http://127.0.0.1:8000/api/v1/seats	None
// 10	View Single Seat	GET	    http://127.0.0.1:8000/api/v1/seats/1	None
// 11	Activate/Deactivate Seat	PATCH	http://127.0.0.1:8000/api/v1/seats/1/status	None

// 3. Booking URLs
// #	Action	Method	URL	Body (raw JSON)
// 13	Book a Seat	POST	    http://127.0.0.1:8000/api/v1/bookings	{"student_id": 1, "seat_id": 1, "booking_date": "2026-09-29", "slot": "06:00-09:00"}
// 14	List All Bookings	GET	http://127.0.0.1:8000/api/v1/bookings	None
// 15	Filter by Date	GET	    http://127.0.0.1:8000/api/v1/bookings?date=2026-09-29	None
// 16	Filter by Slot	GET	    http://127.0.0.1:8000/api/v1/bookings?slot=06:00-09:00	None
// 18	Cancel Booking	POST	http://127.0.0.1:8000/api/v1/bookings/1/cancel	None
// 12	Check Seat Availability	GET	http://127.0.0.1:8000/api/v1/seats/available?date=2026-09-29&slot=06:00-09:00	None

// 19	View Library Dashboard	GET	http://127.0.0.1:8000/api/v1/library/dashboard	None