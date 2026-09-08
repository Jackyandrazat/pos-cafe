<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentAccount;
use Illuminate\Http\JsonResponse;

class PaymentAccountController extends Controller
{
    /**
     * Ambil daftar rekening bank transfer dan akun e-wallet aktif.
     */
    public function index(): JsonResponse
    {
        $accounts = PaymentAccount::query()
            ->active()
            ->get();

        $bankTransfers = $accounts->where('type', 'bank_transfer')->values()->map(fn ($acc) => [
            'id'             => $acc->id,
            'type'           => $acc->type,
            'provider_code'  => $acc->provider_code,
            'name'           => $acc->name,
            'account_number' => $acc->account_number,
            'account_name'   => $acc->account_name,
            'instructions'   => $acc->instructions,
            'qr_image_url'   => $acc->qr_image_url,
        ]);

        $ewallets = $accounts->where('type', 'ewallet')->values()->map(fn ($acc) => [
            'id'             => $acc->id,
            'type'           => $acc->type,
            'provider_code'  => $acc->provider_code,
            'name'           => $acc->name,
            'account_number' => $acc->account_number,
            'account_name'   => $acc->account_name,
            'instructions'   => $acc->instructions,
            'qr_image_url'   => $acc->qr_image_url,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'bank_transfers' => $bankTransfers,
                'ewallets'       => $ewallets,
            ],
        ]);
    }
}
