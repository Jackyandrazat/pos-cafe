<?php

namespace App\Http\Controllers;

use App\Models\CafeTable;
use App\Services\TableQrService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TableQrController extends Controller
{
    public function __construct(
        protected TableQrService $qrService
    ) {
    }

    /**
     * Tampilkan halaman cetak kartu / table tent QR untuk 1 meja.
     */
    public function printSingle(Request $request, CafeTable $table)
    {
        $card = $this->qrService->getTableCardData($table, 260);

        return view('table-qr.single', [
            'card' => $card,
        ]);
    }

    /**
     * Tampilkan halaman cetak massal (bulk) untuk seluruh meja.
     */
    public function printAll(Request $request)
    {
        $tables = CafeTable::with('area')
            ->orderBy('table_number')
            ->get();

        $cards = $tables->map(function (CafeTable $table) {
            return $this->qrService->getTableCardData($table, 200);
        });

        return view('table-qr.bulk', [
            'cards'     => $cards,
            'cafe_name' => function_exists('setting') ? (setting('receipt_cafe_name') ?: setting('cafe_name', config('app.name', 'PSAN'))) : config('app.name', 'PSAN'),
        ]);
    }

    /**
     * Download file vector SVG untuk meja tertentu.
     */
    public function downloadSvg(Request $request, CafeTable $table)
    {
        $svg = $this->qrService->generateSvg($table, 400);
        $filename = 'qr-meja-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $table->table_number) . '.svg';

        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
