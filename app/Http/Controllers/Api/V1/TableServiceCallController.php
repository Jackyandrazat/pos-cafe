<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableServiceCallController extends Controller
{
    /**
     * Tamu di meja memanggil pelayan / meminta bantuan.
     */
    public function callWaiter(Request $request, string $tableNumber): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $table = CafeTable::where('table_number', $tableNumber)
            ->orWhere('id', is_numeric($tableNumber) ? (int) $tableNumber : 0)
            ->first();

        if (! $table) {
            return response()->json([
                'success' => false,
                'message' => "Meja '{$tableNumber}' tidak ditemukan.",
            ], 404);
        }

        $reason = $validated['reason'] ?? 'bantuan';
        $notes = $validated['notes'] ?? null;

        $table->callWaiter($reason, $notes);

        return response()->json([
            'success' => true,
            'message' => "Panggilan pelayan untuk Meja {$table->table_number} berhasil dikirim.",
            'data' => [
                'table_number' => $table->table_number,
                'calling_waiter' => true,
                'reason' => $table->waiter_call_reason,
                'reason_label' => $table->getWaiterCallReasonLabel(),
                'notes' => $table->waiter_call_notes,
                'called_at' => optional($table->waiter_called_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Batalkan panggilan pelayan dari sisi pelanggan.
     */
    public function cancelWaiter(string $tableNumber): JsonResponse
    {
        $table = CafeTable::where('table_number', $tableNumber)
            ->orWhere('id', is_numeric($tableNumber) ? (int) $tableNumber : 0)
            ->first();

        if (! $table) {
            return response()->json([
                'success' => false,
                'message' => "Meja '{$tableNumber}' tidak ditemukan.",
            ], 404);
        }

        $table->dismissWaiterCall();

        return response()->json([
            'success' => true,
            'message' => "Panggilan pelayan untuk Meja {$table->table_number} telah dibatalkan.",
            'data' => [
                'table_number' => $table->table_number,
                'calling_waiter' => false,
            ],
        ]);
    }

    /**
     * Cek status aktif panggilan meja.
     */
    public function callStatus(string $tableNumber): JsonResponse
    {
        $table = CafeTable::where('table_number', $tableNumber)
            ->orWhere('id', is_numeric($tableNumber) ? (int) $tableNumber : 0)
            ->first();

        if (! $table) {
            return response()->json([
                'success' => false,
                'message' => "Meja '{$tableNumber}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'table_number' => $table->table_number,
                'calling_waiter' => (bool) $table->calling_waiter,
                'reason' => $table->waiter_call_reason,
                'reason_label' => $table->getWaiterCallReasonLabel(),
                'notes' => $table->waiter_call_notes,
                'called_at' => optional($table->waiter_called_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Dapatkan detail meja untuk verifikasi koneksi scan QR di Cafe Order Hub.
     */
    public function showTable(string $tableNumber): JsonResponse
    {
        $table = CafeTable::with('area')
            ->where('table_number', $tableNumber)
            ->orWhere('id', is_numeric($tableNumber) ? (int) $tableNumber : 0)
            ->first();

        if (! $table) {
            return response()->json([
                'success' => false,
                'message' => "Meja '{$tableNumber}' tidak ditemukan di sistem.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $table->id,
                'table_number' => $table->table_number,
                'status' => $table->status,
                'capacity' => (int) ($table->capacity ?? 2),
                'area_name' => $table->area?->name ?? 'Area Utama',
                'calling_waiter' => (bool) $table->calling_waiter,
            ],
        ]);
    }
}
