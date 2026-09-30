<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
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
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => ['required', 'string', 'unique:students,mobile', 'unique:users,mobile'],
                'email' => ['required', 'email', 'unique:students,email', 'unique:users,email'],
                'password' => ['nullable', 'string', 'min:6'],
            ]);

            $student = DB::transaction(function () use ($request) {
                $student = Student::create([
                    'name' => $request->name,
                    'mobile' => $request->mobile,
                    'email' => $request->email,
                    'status' => 'active',
                ]);

                $user = User::updateOrCreate(
                    ['email' => $request->email],
                    [
                        'name' => $request->name,
                        'mobile' => $request->mobile,
                        'status' => 'active',
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
    public function update(Request $request, $id)
    {
        try {
            $student = Student::find($id);

            if (! $student) {
                return response()->json(['status' => 'error', 'message' => 'Student not found'], 404);
            }

            $student->name = $request->name ?? $student->name;
            $student->mobile = $request->mobile ?? $student->mobile;
            $student->email = $request->email ?? $student->email;
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

            $student->status = 'inactive';
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

            $student->status = 'active';
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