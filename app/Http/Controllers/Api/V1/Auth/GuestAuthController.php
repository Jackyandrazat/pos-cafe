<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuestAuthController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::create([
            'name' => $data['name'] ?? 'Guest User',
            'email' => sprintf('guest+%s@guest.local', Str::uuid()),
            'phone' => $data['phone'] ?? null,
            'password' => Str::password(),
            'is_guest' => true,
        ]);

        $token = $user->createToken('guest')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'is_guest' => $user->is_guest,
            ],
        ], 201);
    }
}
