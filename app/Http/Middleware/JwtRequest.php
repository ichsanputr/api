<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtRequest
{
    public function handle($request, Closure $next)
    {
        if ($request->input('ip') && $request->bearerToken() == 'undefined') {
            $access = \App\Models\Access::firstOrCreate(
                ['ip_address' => $request->input('ip')],
                [$request->input('platform') => Carbon::now()]
            );
    
            if ($request->query('platform') === 'fillme') {
                $fillmeDate = Carbon::parse($access->fillme);
                if ($fillmeDate->greaterThanOrEqualTo(Carbon::now()->subDay())) {
                    return $next($request);
                } else {
                    return response()->json(['message' => 'Free trial end time'], 403);
                }
            }
    
            return $next($request);
        }

        $jwt = $request->bearerToken();

        if (!$jwt)
            return response()->json(['message' => 'Unauthorized'], 401);

        try {
            JWT::$leeway = 15;

            $decoded = JWT::decode($jwt, new Key(env('JWT_SECRET'), 'HS256'));
            $token = json_decode(json_encode($decoded), true);

            $user = \App\Models\User::where('uuid', $token['account_id'])->first();

            $request->attributes->set('accountDetail', $user);

            if ($user)
                return $next($request);
            else
                return response()->json(['message' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}