<?php

namespace App\Extensions\MinecraftTools\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    protected function getServer(Request $request)
    {
        return $request->server ?? $request->user()->servers()->findOrFail($request->route('server'));
    }

    protected function successResponse(array $data = [], int $status = 200)
    {
        return response()->json(array_merge(['status' => 'success'], $data), $status);
    }

    protected function errorResponse(string $message, int $status = 400, array $data = [])
    {
        return response()->json(array_merge(['status' => 'error', 'message' => $message], $data), $status);
    }

    protected function paginatedResponse($items, int $perPage = 50)
    {
        $page = request()->query('page', 1);
        $total = count($items);
        $offset = ($page - 1) * $perPage;
        
        return [
            'data' => array_slice($items, $offset, $perPage),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ];
    }
}