<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('students')->where('status', 'active')->update(['status' => true]);
        DB::table('students')->where('status', 'inactive')->update(['status' => false]);
        DB::table('students')->where('status', '0')->update(['status' => false]);
        DB::table('students')->where('status', '1')->update(['status' => true]);

        DB::table('users')->where('status', 'active')->update(['status' => true]);
        DB::table('users')->where('status', 'inactive')->update(['status' => false]);
        DB::table('users')->where('status', '0')->update(['status' => false]);
        DB::table('users')->where('status', '1')->update(['status' => true]);

        Schema::table('students', function (Blueprint $table) {
            $table->boolean('status')->default(true)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->boolean('status')->default(true)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->change();
            }
        });
    }
};
