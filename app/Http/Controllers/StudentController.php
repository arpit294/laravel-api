<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

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
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|unique:students,mobile',
            'email' => 'required|email|unique:students,email',
        ]);

        $student = new Student;
        $student->name = $request->name;
        $student->mobile = $request->mobile;
        $student->email = $request->email;
        $student->status = 'active';
        $student->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Student Registered Successfully',
            'data' => $student,
        ], 201);
    }

    // View single student
    public function show($id)
    {
        $student = Student::find($id);

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $student,
        ]);
    }

    // Update student
    public function update(Request $request, $id)
    {
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
    }

    // Deactivate student
    public function deactivate($id)
    {
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
    }


 public function activate($id)
    {
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