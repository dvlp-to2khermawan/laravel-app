<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // Although not strictly needed for this simple method, it's good practice to include.

class HealthCheckController extends Controller
{
    public function check(Request $request)
    {
        return response()->json(['status' => 'API is healthy and running']);
    }
}
