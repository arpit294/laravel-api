<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    // List students
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'students' => Student::all(),
        ]);
    }

    // Register a student
    public function store(StudentRequest $request)
    {
        try {

            $student = DB::transaction(function () use ($request) {
                $student = Student::create([
                    'name' => $request->name,
                    'mobile' => $request->mobile,
                    'email' => $request->email,
                    'status' => true,
                ]);

                $user = User::updateOrCreate(
                    ['email' => $request->email],
                    [
                        'name' => $request->name,
                        'mobile' => $request->mobile,
                        'status' => true,
                        'password' => bcrypt($request->password ?? 'Student@123'),
                    ]
                );

                return [
                    'student' => $student,
                    'user' => $user,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Student Registered Successfully',
                'data' => $student,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register student.',
                'error' => $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    // View single student
    public function show($id)
    {
        $student = Student::find($id);

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $student,
        ]);
    }

    // Update student
    public function update(StudentRequest $request, $id)
    {
        try {
            $student = Student::find($id);

            if (! $student) {
                return response()->json(['status' => 'error', 'message' => 'Student not found'], 404);
            }

            $student->name = $request->name ?? $student->name;
            $student->mobile = $request->mobile ?? $student->mobile;
            $student->email = $request->email ?? $student->email;
            $student->status = $request->has('status') ? (bool) $request->status : $student->status;
            $student->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Student Updated Successfully',
                'data' => $student,
            ]);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update student.',
                'data' => null,
            ]);
        }
    }

    // Deactivate student
    public function deactivate($id)
    {
        try {
            $student = Student::find($id);

            if (! $student) {
                return response()->json(['status' => 'error', 'message' => 'Student not found'], 404);
            }

            $student->status = false;
            $student->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Student Deactivated Successfully',
                'data' => $student,
            ]);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to deactive student.',
                'data' => null,
            ]);
        }
    }

    public function activate($id)
    {
        try {
            $student = Student::find($id);

            if (! $student) {
                return response()->json(['status' => 'error', 'message' => 'Student not found'], 404);
            }

            $student->status = true;
            $student->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Student activated Successfully',
                'data' => $student,
            ]);
        } catch (Exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to active student.',
                'data' => null,
            ]);
        }
    }

    // Student booking history
    public function bookingHistory($id)
    {
        $student = Student::with('bookings.seat')->find($id);

        if (! $student) {
            return response()->json(['status' => 'error', 'message' => 'Student not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'student' => $student->name,
            'bookings' => $student->bookings,
        ]);
    }
}
