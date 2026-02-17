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
        if ($order->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have access to this order.');
        }

        return new OrderStatusResource($order->load('statusLogs'));
    }
    public function timeline(Order $order)
    {
        return response()->json([
            'data' => $order->timelines()->latest()->get()
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());

        return new UpdateOrderStatusResource($order);
    }
}
