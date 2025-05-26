<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            // Make sure $user is an instance of App\Models\User
            if (!($user instanceof User)) {
                // This case should ideally not happen if Auth::attempt is successful
                // and your user provider is configured correctly.
                return response()->json(['error' => 'Authentication failed or user model is incorrect.'], 500);
            }
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }
}
