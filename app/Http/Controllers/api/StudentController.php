<?php

namespace App\Http\Controllers\api;

use App\Helpers\reply;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    // List students
    public function index()
    {
        return reply::successWith(StudentResource::collection(User::all()), 'Students fetched successfully');
    }

    // Register a student
    public function store(StudentRequest $request)
    {
        try {

            $student = DB::transaction(function () use ($request) {
                return User::create([
                    'name' => $request->name,
                    'mobile' => $request->mobile,
                    'email' => $request->email,
                    'status' => true,
                    'password' => $request->password ?? 'Student@123',
                ]);
            });

            return reply::successWith(new StudentResource($student), 'Student Registered Successfully');

        } catch (Exception $e) {
            return reply::errorWith(['error' => $e->getMessage()], 'Failed to register student.');
        }
    }

    // View single studen
    public function show($id)
    {
        $student = User::find($id);

        if (! $student) {
            return reply::errorWith(null, 'Student not found', 404);
        }

        return reply::successWith(new StudentResource($student), 'Student fetched successfully');
    }

    // Update student
    public function update(StudentRequest $request, $id)
    {
        try {
            $student = User::find($id);

            if (! $student) {
                return reply::errorWith(null, 'Student not found', 404);
            }

            $student->name = $request->name ?? $student->name;
            $student->mobile = $request->mobile ?? $student->mobile;
            $student->email = $request->email ?? $student->email;
            $student->status = $request->has('status') ? (bool) $request->status : $student->status;
            $student->save();

            return reply::successWith(new StudentResource($student), 'Student Updated Successfully');
        } catch (Exception $e) {
            return reply::errorWith(['error' => $e->getMessage()], 'Failed to update student.');
        }
    }

    // Deactivate student
    public function deactivate($id)
    {
        try {
            $student = User::find($id);

            if (! $student) {
                return reply::errorWith(null, 'Student not found', 404);
            }

            $student->status = false;
            $student->save();

            return reply::successWith(new StudentResource($student), 'Student Deactivated Successfully');
        } catch (Exception $e) {
            return reply::errorWith(['error' => $e->getMessage()], 'Failed to deactive student.');
        }
    }

    public function activate($id)
    {
        try {
            $student = User::find($id);

            if (! $student) {
                return reply::errorWith(null, 'Student not found', 404);
            }

            $student->status = true;
            $student->save();

            return reply::successWith(new StudentResource($student), 'Student activated Successfully');
        } catch (Exception $e) {
            return reply::errorWith(['error' => $e->getMessage()], 'Failed to active student.');
        }
    }

    // Student booking history
    public function bookingHistory($id)
    {
        $student = User::with('bookings.seat')->find($id);

        if (! $student) {
            return reply::errorWith(null, 'Student not found', 404);
        }

        return reply::successWith([
            'student' => $student->name,
            'bookings' => $student->bookings,
        ], 'Student booking history fetched successfully');
    }
}
