<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StoreConfigController extends Controller
{
    /**
     * Dapatkan konfigurasi toko/kafe untuk Cafe Order Hub.
     * Termasuk izin metode pembayaran tunai & koordinat geofencing.
     */
    public function show(): JsonResponse
    {
        $cafeName = function_exists('setting')
            ? (setting('receipt_cafe_name') ?: setting('cafe_name', config('app.name', 'PSAN')))
            : config('app.name', 'PSAN');

        $allowCash = function_exists('setting')
            ? filter_var(setting('self_order_allow_cash', true), FILTER_VALIDATE_BOOLEAN)
            : true;

        $lat = function_exists('setting') ? setting('cafe_latitude') : null;
        $lng = function_exists('setting') ? setting('cafe_longitude') : null;
        $radius = function_exists('setting') ? (int) setting('cafe_geofence_radius', 100) : 100;

        $geofenceEnabled = ! empty($lat) && ! empty($lng) && $radius > 0;

        return response()->json([
            'success' => true,
            'data' => [
                'cafe_name'             => $cafeName,
                'self_order_allow_cash' => $allowCash,
                'geofence'              => [
                    'enabled'       => $geofenceEnabled,
                    'latitude'      => $geofenceEnabled ? (float) $lat : null,
                    'longitude'     => $geofenceEnabled ? (float) $lng : null,
                    'radius_meters' => $radius,
                ],
            ],
        ]);
    }
}
