<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtRequest
{
    public function handle($request, Closure $next)
    {
        $jwt = null;
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches))
            $jwt = $matches[1];

        if (!$jwt)
            return response()->json(['message' => 'Unauthorized'], 401);

        try {
            JWT::$leeway = 15;

            $secret_key = base64_decode(env('JWT_SECRET'));
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS512'));
            $token = json_decode(json_encode($decoded), true);

            $user = \App\Models\User::where('uuid', $token['']['account_id'])->first();

            $request->attributes->set('accountDetail', ['workspaceid' => 'halo']);

            if ($user)
                return $next($request);
            else
                return response()->json(['message' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}