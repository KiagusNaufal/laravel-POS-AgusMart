<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .struk {
            width: 250px;
            margin: auto;
            padding: 10px;
            border: 1px solid #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        .btn-cetak {
            margin-top: 10px;
        }
        @media print {
            .btn-cetak {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="struk">
        <h3>Struk Pembayaran</h3>
        <p>ID Transaksi: <strong>{{ $penjualan->no_faktur }}</strong></p>
        <p>Tanggal: <strong>{{ $penjualan->created_at->format('d-m-Y H:i') }}</strong></p>

        <table>
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->detail_penjualan as $detail)
                <tr>
                    <td>{{ $detail->barang->nama_barang }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Total Harga: Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong></p>
        <p><strong>Cash: Rp {{ number_format(request('cash'), 0, ',', '.') }}</strong></p>
        <p><strong>Kembalian: Rp {{ number_format(request('kembalian'), 0, ',', '.') }}</strong></p>

        <button onclick="cetakStruk()">Cetak Struk</button>
    </div>

</body>
</html>

<script>
    function cetakStruk() {
        window.print(); // Cetak struk
        setTimeout(function() {
            window.location.href = "{{ route('admin.penjualan') }}"; // Redirect ke halaman penjualan
        }, 1000); // Redirect setelah 1 detik
    }
</script>
