<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
        ]);

        return DB::transaction(function () use ($data) {

            // 1️⃣ Cari customer
            $customer = Customer::where('phone', $data['phone'])->first();

            if (!$customer) {
                return response()->json([
                    'message' => 'Customer not found'
                ], 404);
            }

            // 2️⃣ Cek apakah sudah punya user
            $user = User::where('customer_id', $customer->id)->first();

            if (!$user) {
                // 3️⃣ Buat user baru dari customer
                $user = User::create([
                    'name' => $customer->name,
                    'email' => $customer->email ?? 'customer+'.Str::uuid().'@customer.local',
                    'phone' => $customer->phone,
                    'password' => bcrypt(Str::random(40)),
                    'is_guest' => false,
                    'customer_id' => $customer->id,
                ]);
            }

            // 4️⃣ Revoke token lama khusus self-order
            $user->tokens()
                ->where('name', 'self-order')
                ->delete();

            // 5️⃣ Buat token baru
            $token = $user
                ->createToken('self-order', ['self-order'])
                ->plainTextToken;

            return response()->json([
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'is_guest' => $user->is_guest,
                ],
                'customer' => [
                    'id' => $customer->id,
                    'points' => $customer->points,
                    'lifetime_value' => $customer->lifetime_value,
                ]
            ]);
        });
    }
}

