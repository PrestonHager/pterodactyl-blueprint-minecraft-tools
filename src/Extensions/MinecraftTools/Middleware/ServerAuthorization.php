<?php

namespace App\Extensions\MinecraftTools\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServerAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        $serverUuid = $request->route('server');
        
        if (!$serverUuid) {
            return response()->json(['error' => 'Server not specified'], 400);
        }

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $server = $user->servers()->where('uuid', $serverUuid)->first();
        
        if (!$server) {
            return response()->json(['error' => 'Server not found or access denied'], 403);
        }

        $request->merge(['server' => $server]);

        return $next($request);
    }
}