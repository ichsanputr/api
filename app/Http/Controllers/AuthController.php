<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Validator;

class AuthController extends Controller
{
    public function register(\App\Http\Requests\RegisterRequest $request)
    {
        \App\Models\User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'pic_url' => $request->pic_url,
        ]);

        return response()->json([
            'message' => 'User created successfully',
        ]);
    }

    public function login(\App\Http\Requests\LoginRequest $request)
    {
        $user = \App\Models\User::where('email', $request->input('username'))->first();

        if ($user && Hash::check($request->input('password'), $user->password)) {
            $issuedAt = time();
            $expirationTime = $issuedAt + (3 * 24 * 3600);
            $payload = [
                'account_id' => $user->uuid,
                'account' => $user->username,
                'iat' => $issuedAt,
                'exp' => $expirationTime
            ];

            $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
            ]);
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }
}
