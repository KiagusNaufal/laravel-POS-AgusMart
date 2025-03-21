@extends('layouts.admin')

@section('main-content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Barang</h6>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('admin.laporan.penjualan') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="no_faktur">No Faktur</label>
                            <input type="text" class="form-control" id="no_faktur" name="no_faktur" placeholder="Masukkan No Faktur" value="{{ request('no_faktur') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3 mb-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>
                <form method="GET" action="{{ route('admin.laporan.penjualan.pdf') }}">
                    @csrf
                    <input type="hidden" name="no_faktur" value="{{ request('no_faktur') }}">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <button type="submit" class="btn btn-danger btn-block mt-2">Download PDF</button>
                </form>
                <form method="GET" action="{{ route('admin.laporan.penjualan.excel') }}">
                    @csrf
                    <input type="hidden" name="no_faktur" value="{{ request('no_faktur') }}">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <button type="submit" class="btn btn-success btn-block mt-2">Download Excel</button>
                </form>
            </div>

            <div class="table-responsive p-3">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Faktur</th>
                                <th>Tanggal Faktur</th>
                                <th>Total</th>
                                <th>Nama Pelanggan</th>
                                <th>Input</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penjualan as $item)
                            <tr>
                            <td>{{ $loop->iteration + $penjualan->firstItem() - 1 }}</td>
                                <td>{{ $item->no_faktur }}</td>
                                <td>{{ $item->tanggal_faktur }}</td>
                                <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                <td>{{ $item->member->nama_pelanggan ?? 'Tidak ada member' }}</td>
                                <td>{{ $item->user->name }}</td>
                                <td>
    <button class="btn btn-info btn-detail"
            data-bs-toggle="modal"
            data-bs-target="#detailModal"
            data-item='@json($item->load("detail_penjualan.barang"))'>
        Detail
    </button>
</td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>  
                <div class="d-flex justify-content-center">
    {{ $penjualan->appends(request()->except('page'))->links() }}
</div>

            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Kode Masuk:</strong> <span id="noFaktur"></span></p>
                <p><strong>Tanggal Masuk:</strong> <span id="tanggalFaktur"></span></p>
                <p><strong>member:</strong> <span id="member"></span></p>
                <p><strong>Input:</strong> <span id="input"></span></p>

                <h5>Detail Barang:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Harga Beli</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detailBarang"></tbody>
                    </table>
                </div>

                <p><strong>Total:</strong> <span id="total"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".btn-detail").forEach((button) => {
        button.addEventListener("click", function () {
            const itemData = this.getAttribute("data-item");

            if (!itemData) {
                console.error("Data item tidak ditemukan!");
                return;
            }

            const item = JSON.parse(itemData);

            document.getElementById("noFaktur").textContent = item.no_faktur;
            document.getElementById("tanggalFaktur").textContent = item.tanggal_faktur;
            document.getElementById("member").textContent = item.member ? item.member.nama_pelanggan : 'Tidak ada member';
            document.getElementById("input").textContent = item.user.name;

            const detailBarang = document.getElementById("detailBarang");
            detailBarang.innerHTML = "";

            let total = 0;
            if (item.detail_penjualan.length > 0) {
                item.detail_penjualan.forEach((detail) => {
                    const tr = document.createElement("tr");

                    tr.innerHTML = `
                        <td>${detail.barang.nama_barang}</td>
                        <td>${detail.jumlah}</td>
                        <td>Rp ${new Intl.NumberFormat("id-ID").format(detail.barang.harga_beli)}</td>
                        <td>Rp ${new Intl.NumberFormat("id-ID").format(detail.sub_total)}</td>
                    `;

                    detailBarang.appendChild(tr);
                    total += detail.sub_total;
                });
            } else {
                const emptyRow = document.createElement("tr");
                emptyRow.innerHTML = `<td colspan="4" class="text-center">Tidak ada data barang</td>`;
                detailBarang.appendChild(emptyRow);
            }

            document.getElementById("total").textContent = `Rp ${new Intl.NumberFormat("id-ID").format(total)}`;
        });
    });
});

</script>
    
@endsection