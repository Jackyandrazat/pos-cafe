<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h2, h4 {
            text-align: center;
            margin: 0;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h2>LAPORAN ORDER</h2>
        <h4>{{ date('d-m-Y') }}</h4>
    </div>

    <table>
        <thead>
            <tr>
                <th>No. Order</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Status</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($orders as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $row->customer_name ?? '-' }}</td>
                    <td>{{ ucfirst($row->status) }}</td>
                    <td style="text-align:right;">{{ number_format($row->total_order, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += $row->total_order; @endphp
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada data order</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;">Total Keseluruhan</td>
                <td style="text-align:right;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>
        <br><br><br>
        <p>__________________________</p>
        <p><em>Penanggung Jawab</em></p>
    </div>
</body>
</html>
