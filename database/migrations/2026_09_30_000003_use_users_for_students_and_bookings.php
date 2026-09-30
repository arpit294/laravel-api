<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('students')) {
            $studentUserIds = [];

            foreach (DB::table('students')->get() as $student) {
                $user = DB::table('users')->where('email', $student->email)->first();

                if (! $user) {
                    $userId = DB::table('users')->insertGetId([
                        'name' => $student->name,
                        'email' => $student->email,
                        'mobile' => $student->mobile,
                        'status' => (bool) $student->status,
                        'password' => Hash::make(Str::random(40)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $userId = $user->id;
                    if ($user->mobile === null) {
                        DB::table('users')->where('id', $userId)->update(['mobile' => $student->mobile]);
                    }
                }

                $studentUserIds[$student->id] = $userId;
            }

            if (Schema::hasColumn('bookings', 'student_id')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable();
                });

                foreach (DB::table('bookings')->select('id', 'student_id')->get() as $booking) {
                    if (! isset($studentUserIds[$booking->student_id])) {
                        throw new RuntimeException("Booking {$booking->id} has no matching user.");
                    }

                    DB::table('bookings')->where('id', $booking->id)->update([
                        'user_id' => $studentUserIds[$booking->student_id],
                    ]);
                }

                Schema::table('bookings', function (Blueprint $table) {
                    $table->dropForeign(['student_id']);
                    $table->dropColumn('student_id');
                });

                Schema::table('bookings', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable(false)->change();
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }

            Schema::dropIfExists('students');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('mobile')->nullable()->unique();
                $table->string('email')->unique();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });

            foreach (DB::table('users')->get() as $user) {
                DB::table('students')->insert([
                    'name' => $user->name,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'status' => (bool) $user->status,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        }

        if (Schema::hasColumn('bookings', 'user_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('student_id')->nullable();
            });

            $userStudentIds = DB::table('users')
                ->join('students', 'students.email', '=', 'users.email')
                ->pluck('students.id', 'users.id');

            foreach (DB::table('bookings')->select('id', 'user_id')->get() as $booking) {
                DB::table('bookings')->where('id', $booking->id)->update([
                    'student_id' => $userStudentIds[$booking->user_id],
                ]);
            }

            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });

            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('student_id')->nullable(false)->change();
                $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            });
        }
    }
};