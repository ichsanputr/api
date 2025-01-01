<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    public function register(\App\Http\Requests\RegisterRequest $request)
    {
        \App\Models\User::create([
            'name' => $request->name,
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
        $user = \App\Models\User::where('email', $request->input('name'))->first();

        if ($user && Hash::check($request->input('password'), $user->password)) {
            $issuedAt = time();
            $expirationTime = $issuedAt + (3 * 24 * 3600);
            $payload = [
                'account_id' => $user->uuid,
                'account' => $user->name,
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

    public function profile(Request $request)
    {   
        try {
            $userid = $request->attributes->get('accountDetail')['uuid'];
            $userprofile = \App\Models\User::where('uuid', $userid)->first(['name', 'email', 'active_until', 'pic_url']);

            if (!$userprofile) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $userprofile,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {   
        try {
            $userid = $request->attributes->get('accountDetail')['uuid'];
            $userprofile = \App\Models\User::where('uuid', $userid)->first(['name', 'email']);

            if (!$userprofile) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            \App\Models\User::where('uuid', $userid)->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'pic_url' => $request->input('pic_url'),
            ]);

            return response()->json([
                'status' => true,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
