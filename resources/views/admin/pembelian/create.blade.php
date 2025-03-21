@extends('layouts.admin')

@section('main-content')
<div class="container">
    <h2 class="mt-4 mb-3">Transaksi Pembelian</h2>
    @if ($errors->any())
            <div class="alert alert-danger border-left-danger" role="alert">
                <ul class="pl-4 my-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.pembelian.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="id_pemasok">Pilih Pemasok</label>
                    <div class="input-group">
                        <input type="text" id="nama_pemasok" class="form-control" readonly placeholder="Klik untuk memilih pemasok">
                        <input type="hidden" id="id_pemasok" name="id_pemasok">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#vendorModal">Pilih</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="items">Pilih Barang</label>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#itemModal">Pilih Barang</button>
                </div>

                <table class="table table-bordered" id="selectedItemsTable">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Harga Beli</th>
                            <th>Stok</th>
                            <th>Sub Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Selected items will be added here -->
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success">Simpan Transaksi</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pemasok -->
<div class="modal fade" id="vendorModal" tabindex="-1" aria-labelledby="vendorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Pemasok</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" id="searchVendor" class="form-control mb-2" placeholder="Cari pemasok...">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="vendorList">
                        <!-- Data pemasok akan muncul di sini -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Barang -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" id="searchItem" class="form-control mb-2" placeholder="Cari barang...">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemList">
                        <!-- Data barang akan muncul di sini -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('searchVendor').addEventListener('input', function () {
    const query = this.value;

    fetch(`{{ route('admin.pembelian.search-pemasok') }}?q=${query}`)
        .then(response => response.json())
        .then(data => {
            const vendorList = document.getElementById('vendorList');
            vendorList.innerHTML = '';

            if (data.length === 0) {
                vendorList.innerHTML = `<tr><td colspan="3" class="text-center">Tidak ada hasil</td></tr>`;
                return;
            }

            data.forEach(vendor => {
                let row = document.createElement('tr');
                row.innerHTML = `
                    <td>${vendor.nama_pemasok}</td>
                    <td>${vendor.alamat}</td>
                    <td><button class="btn btn-sm btn-info pilih-vendor" data-id="${vendor.id}" data-nama="${vendor.nama_pemasok}">Pilih</button></td>
                `;
                vendorList.appendChild(row);
            });

            document.querySelectorAll('.pilih-vendor').forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('id_pemasok').value = this.getAttribute('data-id');
                    document.getElementById('nama_pemasok').value = this.getAttribute('data-nama');
                    $('#vendorModal').modal('hide');
                });
            });
        });
});

document.getElementById('searchItem').addEventListener('input', function () {
    const query = this.value;

    fetch(`{{ route('admin.pembelian.search') }}?q=${query}`)
        .then(response => response.json())
        .then(data => {
            const itemList = document.getElementById('itemList');
            itemList.innerHTML = '';

            if (data.length === 0) {
                itemList.innerHTML = `<tr><td colspan="3" class="text-center">Tidak ada hasil</td></tr>`;
                return;
            }

            const selectedItems = Array.from(document.querySelectorAll('#selectedItemsTable tbody tr')).map(row => row.cells[0].innerText);

            data.forEach(item => {
                if (!selectedItems.includes(item.id)) {
                    let row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.kode_barang}</td>
                        <td>${item.nama_barang}</td>
                        <td><button class="btn btn-sm btn-info pilih-item" data-kode="${item.kode_barang}" data-nama="${item.nama_barang}" data-id="${item.id}">Pilih</button></td>
                    `;
                    itemList.appendChild(row);
                }
            });

            document.querySelectorAll('.pilih-item').forEach(button => {
                button.addEventListener('click', function () {
                    const itemCode = this.getAttribute('data-kode');
                    const itemId = this.getAttribute('data-id');
                    const itemName = this.getAttribute('data-nama');
                    const table = document.getElementById('selectedItemsTable').getElementsByTagName('tbody')[0];

                    // Check if item is already selected
                    if (Array.from(table.rows).some(row => row.cells[0].innerText === itemCode)) {
                        alert('Barang sudah dipilih');
                        return;
                    }

                    let row = table.insertRow();
                    row.innerHTML = `
                        <td>${itemCode}<input type="hidden" name="id_barang[]" value="${itemId}"></td>
                        <td>${itemName}<input type="hidden" name="items[]" value="${itemName}"></td>
                        <td><div class="input-group"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" name="harga_beli[]" class="form-control harga_beli" required></div></td>
                        <td><input type="number" name="jumlah[]" class="form-control jumlah" required></td>
                        <td class="sub_total">Rp 0</td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-item">Hapus</button></td>
                    `;
                    $('#itemModal').modal('hide');

                    document.querySelectorAll('.remove-item').forEach(button => {
                        button.addEventListener('click', function () {
                            this.closest('tr').remove();
                            calculateSubTotal();
                        });
                    });

                    document.querySelectorAll('.harga_beli, .jumlah').forEach(input => {
                        input.addEventListener('input', function () {
                            calculateSubTotal();
                        });
                    });

                    calculateSubTotal();
                });
            });
        });
});

function calculateSubTotal() {
    document.querySelectorAll('#selectedItemsTable tbody tr').forEach(row => {
        const hargaBeli = row.querySelector('.harga_beli').value;
        const jumlah = row.querySelector('.jumlah').value;
        const subTotal = row.querySelector('.sub_total');

        subTotal.innerText = `Rp ${hargaBeli * jumlah}`;
    });
}
</script>

@endsection
