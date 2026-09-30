<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentStatusBooleanTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_status_is_stored_and_loaded_as_boolean(): void
    {
        $student = Student::create([
            'name' => 'Ava',
            'mobile' => '9999999999',
            'email' => 'ava@example.com',
            'status' => true,
        ]);

        $this->assertTrue($student->status);

        $freshStudent = Student::findOrFail($student->id);

        $this->assertSame(true, $freshStudent->status);
        $this->assertIsBool($freshStudent->status);
    }
}
