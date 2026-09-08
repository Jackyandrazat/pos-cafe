<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\Api\V1\OrderStatusResource;
use App\Http\Resources\Api\V1\UpdateOrderStatusResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderStatusController extends Controller
{
    public function show(Request $request, Order $order): OrderStatusResource
    {
        $user = $request->user();
        $isStaff = method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'cashier', 'super_admin']);
        if ($order->user_id !== $user->id && ! $isStaff) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this order.');
        }

        return new OrderStatusResource($order->load('statusLogs'));
    }

    public function timeline(Request $request, Order $order)
    {
        $user = $request->user();
        $isStaff = method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'cashier', 'super_admin']);
        if ($order->user_id !== $user->id && ! $isStaff) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this order.');
        }

        return response()->json([
            'data' => $order->statusLogs()->latest()->get()
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());

        return new UpdateOrderStatusResource($order);
    }
}
