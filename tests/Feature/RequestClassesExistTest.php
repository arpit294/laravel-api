<?php

namespace Tests\Feature;

use App\Http\Requests\BookingRequest;
use App\Http\Requests\DashboardRequest;
use App\Http\Requests\SeatRequest;
use App\Http\Requests\StudentRequest;
use Tests\TestCase;

class RequestClassesExistTest extends TestCase
{
    public function test_api_form_requests_exist_for_each_controller(): void
    {
        $this->assertTrue(class_exists(StudentRequest::class));
        $this->assertTrue(class_exists(SeatRequest::class));
        $this->assertTrue(class_exists(BookingRequest::class));
        $this->assertTrue(class_exists(DashboardRequest::class));
    }
}
