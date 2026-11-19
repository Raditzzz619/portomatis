<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if ($request->user()?->role === 'user' && $response->getStatusCode() < 400) {
            $request->user()->activities()->create([
                'action' => $request->route()->getName(), 'step' => $request->route('step'), 'created_at' => now(),
            ]);
        }

        return $response;
    }
}
