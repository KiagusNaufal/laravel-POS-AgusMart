<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            text-align: left;
            background-color: #fff;
            color: #000;
            width: 58mm; /* Sesuaikan dengan lebar printer */
            margin: auto;
            padding: 10px;
            border: 1px solid #000;
        }
        .struk {
            padding: 10px;
            border-bottom: 1px dashed #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 4px 0;
            text-align: left;
        }
        .total {
            font-weight: bold;
            text-align: center;
        }
        .btn-cetak {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        @media print {
            @page {
                size: 58mm auto; /* Lebar 58mm, panjang otomatis */
                margin: 0;
            }
            body {
                font-size: 10px; /* Atur ukuran font lebih kecil */
                zoom: 1; /* Sesuaikan zoom jika diperlukan */
            }
            body * {
                visibility: hidden;
            }
            .struk, .struk * {
                visibility: visible;
            }
            .struk {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                border: none;
            }
            .btn-cetak {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="struk">
        <h3 style="text-align: center;">AgusMart</h3>
        <p>ID Transaksi: <strong>{{ $penjualan->no_faktur }}</strong></p>
        <p>Tanggal: <strong>{{ $penjualan->created_at->format('d-m-Y H:i') }}</strong></p>
        <hr>

        <table>
            <thead>
                <tr>
                    <th>Barang</th>
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
        <hr>
        <p class="total" style="text-align: left;">Total: Rp {{ number_format($penjualan->total, 0, ',', '.') }}</p>
        <p class="total" style="text-align: left;">Cash: Rp {{ number_format(request('cash'), 0, ',', '.') }}</p>
        <p class="total" style="text-align: left;">Kembalian: Rp {{ number_format(request('kembalian'), 0, ',', '.') }}</p>        
        <hr>
        <p style="text-align: center;">*** Terima Kasih ***</p>

        <button class="btn-cetak" onclick="cetakStruk()">Cetak Struk</button>
    </div>

</body>
</html>

<script>
    async function cetakStruk() {
        window.print();
        await setTimeout(function() {
            window.location.href = "{{ route('admin.penjualan') }}";
        }, 1000);

        localStorage.removeItem('cart');
    }
</script>
