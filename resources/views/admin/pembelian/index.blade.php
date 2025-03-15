@extends('layouts.admin')

@section('main-content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif`

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-4">
                <h6 class="m-0 font-weight-bold text-primary mb-2">Data Pembelian Barang</h6>
                <a href="{{ route('admin.pembelian.create') }}" class="btn btn-primary">
                    Restok Barang
                </a>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger border-left-danger" role="alert">
                <ul class="pl-4 my-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Masuk</th>
                                <th>Tanggal Masuk</th>
                                <th>Pemasok</th>
                                <th>Input</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pembelian as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->kode_masuk }}</td>
                                <td>{{ $item->tanggal_masuk }}</td>
                                <td>{{ $item->pemasok->nama_pemasok }}</td>
                                <td>{{ $item->user->name }}</td>
                                <td>
                                <button class="btn btn-info btn-detail"
        data-toggle="modal"
        data-target="#detailModal"
        data-item="{{ $item->load('detail_pembelian.barang')->toJson() }}">
    Detail
</button>


</td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>  
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">

  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Kode Masuk:</strong> <span id="kodeMasuk"></span></p>
                <p><strong>Tanggal Masuk:</strong> <span id="tanggalMasuk"></span></p>
                <p><strong>Pemasok:</strong> <span id="pemasok"></span></p>
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
    $(".btn-detail").on("click", function () {
        const itemData = $(this).data("item");

        if (!itemData) {
            console.error("Data item tidak ditemukan!");
            return;
        }

        // Isi data modal
        $("#kodeMasuk").text(itemData.kode_masuk);
        $("#tanggalMasuk").text(itemData.tanggal_masuk);
        $("#pemasok").text(itemData.pemasok.nama_pemasok);
        $("#input").text(itemData.user.name);

        let detailBarang = "";
        let total = 0;

        if (itemData.detail_pembelian.length > 0) {
            itemData.detail_pembelian.forEach((detail) => {
                detailBarang += `
                    <tr>
                        <td>${detail.barang.nama_barang}</td>
                        <td>${detail.jumlah}</td>
                        <td>Rp ${new Intl.NumberFormat("id-ID").format(detail.barang.harga_beli)}</td>
                        <td>Rp ${new Intl.NumberFormat("id-ID").format(detail.sub_total)}</td>
                    </tr>
                `;
                total += detail.sub_total;
            });
        } else {
            detailBarang = `<tr><td colspan="4" class="text-center">Tidak ada data barang</td></tr>`;
        }

        $("#detailBarang").html(detailBarang);
        $("#total").text(`Rp ${new Intl.NumberFormat("id-ID").format(total)}`);

        // Tampilkan modal manual
        $("#detailModal").modal("show");
        $('#detailModal').on('hidden.bs.modal', function () {
    setTimeout(function() {
        $('body').removeClass('modal-open');
    }, 10); // Delay biar Bootstrap kelola backdrop dengan benar
});

    });
    
});




</script>
    
@endsection