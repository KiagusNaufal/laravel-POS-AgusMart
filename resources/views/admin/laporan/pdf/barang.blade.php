<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Barang</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Laporan Penjualan Barang</h2>
    <div style="text-align: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">AgusMart</h1>
        <p style="margin: 0;">Jl. Contoh Alamat No. 123, Kota Contoh, Indonesia</p>
        <p style="margin: 0;">Telepon: (021) 12345678 | Email: info@agusmart.com</p>
    </div>
    <hr style="border: 1px solid black; margin-bottom: 20px;">
    <h2 style="text-align: center;">Laporan Pembelian</h2>
    <p style="text-align: center;">Periode: {{ request('start_date') }} - {{ request('end_date') }}</p>
    <p style="text-align: center;">Tanggal: {{ date('d M Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Total Penjualan</th>
                <th>Keuntungan</th>
                <th>Total Terjual</th>
            </tr>
        </thead>
        <tbody>
            @foreach($barang as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item['kode_barang'] }}</td>
                <td>{{ $item['nama_barang'] }}</td>
                <td>Rp {{ number_format($item['total_penjualan'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item['keuntungan'], 0, ',', '.') }}</td>
                <td>{{ $item['total_terjual'] }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>{{ array_sum(array_column($barang, 'total_terjual')) }}</strong></td>
                <td><strong>Rp {{ number_format(array_sum(array_column($barang, 'total_penjualan')), 0, ',', '.') }}</strong></td>
                <td><strong>Rp {{ number_format(array_sum(array_column($barang, 'keuntungan')), 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>