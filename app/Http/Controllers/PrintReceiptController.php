<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrintReceiptController extends Controller
{
    public function printCustomer(Request $request, Order $order)
    {
        $this->authorizePrint($request, $order);

        $order->load([
            'items.product',
            'items.size',
            'items.toppings',
            'customer',
            'table',
            'user',
            'payments' => fn ($q) => $q->where('status', 'completed')->latest(),
        ]);

        return view('receipts.thermal', [
            'order' => $order,
            'type' => 'customer',
        ]);
    }

    public function printKitchen(Request $request, Order $order)
    {
        $this->authorizePrint($request, $order);

        $order->load([
            'items.product',
            'items.size',
            'items.toppings',
            'customer',
            'table',
            'user',
        ]);

        return view('receipts.thermal', [
            'order' => $order,
            'type' => 'kitchen',
        ]);
    }

    public function printPayment(Request $request, \App\Models\Payment $payment)
    {
        $order = $payment->order;
        if (! $order) {
            abort(Response::HTTP_NOT_FOUND, 'Pesanan untuk pembayaran ini tidak ditemukan.');
        }

        $this->authorizePrint($request, $order);

        $order->load([
            'items.product',
            'items.size',
            'items.toppings',
            'customer',
            'table',
            'user',
            'payments' => fn ($q) => $q->where('status', 'completed')->orWhere('id', $payment->id)->latest(),
        ]);

        return view('receipts.thermal', [
            'order'   => $order,
            'payment' => $payment,
            'type'    => 'customer',
        ]);
    }

    protected function authorizePrint(Request $request, Order $order): void
    {
        $user = $request->user();
        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $isStaff = method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'cashier', 'super_admin']);
        if ($order->user_id !== $user->id && ! $isStaff) {
            abort(Response::HTTP_FORBIDDEN, 'Akses cetak struk ditolak.');
        }
    }
}
