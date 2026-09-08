<?php

namespace App\Services;

use App\Models\CafeTable;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableQrService
{
    /**
     * Dapatkan base URL dari Cafe Order Hub.
     */
    public function getBaseUrl(): string
    {
        return rtrim(config('services.order_hub.url', env('ORDER_HUB_URL', 'http://localhost:5173')), '/');
    }

    /**
     * Dapatkan target URL untuk scan QR meja spesifik.
     * Format: {ORDER_HUB_URL}/table/{table_number}
     */
    public function getTargetUrl(CafeTable $table): string
    {
        return $this->getBaseUrl() . '/table/' . rawurlencode($table->table_number);
    }

    /**
     * Generate QR code berformat vector SVG yang tajam dan siap cetak.
     */
    public function generateSvg(CafeTable $table, int $size = 260): string
    {
        $targetUrl = $this->getTargetUrl($table);

        return (string) QrCode::format('svg')
            ->size($size)
            ->errorCorrection('H')
            ->margin(1)
            ->generate($targetUrl);
    }

    /**
     * Data lengkap untuk kartu meja / table tent printable.
     */
    public function getTableCardData(CafeTable $table, int $qrSize = 260): array
    {
        return [
            'table'      => $table,
            'target_url' => $this->getTargetUrl($table),
            'qr_svg'     => $this->generateSvg($table, $qrSize),
            'cafe_name'  => function_exists('setting') ? (setting('receipt_cafe_name') ?: setting('cafe_name', config('app.name', 'PSAN'))) : config('app.name', 'PSAN'),
            'area_name'  => $table->area?->name ?? 'Area Utama',
        ];
    }
}
