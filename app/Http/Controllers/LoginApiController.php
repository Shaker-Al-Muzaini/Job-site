<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginApiController extends Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! auth()->attempt($data)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = auth()->user();

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => $token,
        ]);
    }
}
