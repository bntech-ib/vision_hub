<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class ApiThrottleMiddleware extends ThrottleRequests
{
    // Custom API throttling logic if needed
}
