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
            <div class="card-header py-4">
                <h6 class="m-0 font-weight-bold text-primary mb-2">Data Pengajuan Barang</h6>
                <button type="button" class="btn btn-primary" id="addButton">
                    Tambah Pengajuan Barang
                </button>
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
                <form method="GET" action="{{ route('admin.pengajuan.pdf') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-block mt-2">Download PDF</button>
                </form>
                <form method="GET" action="{{ route('admin.pengajuan.excel') }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block mt-2">Download Excel</button>
                </form>
                <div class="table-responsive p-3">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pengaju</th>
                                <th>Nama Barang</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Jumlah</th>
                                <th>Terpenuhi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuan as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->member->nama_pelanggan }}</td>
                                <td>{{ $item->nama_barang }}</td>
                                <td>{{ $item->tanggal_pengajuan }}</td>
                                <td>{{ $item->jumlah }}</td>
                                <td>
                                    <form action="{{ url('admin/pengajuan/status/' . $item->id) }}" method="POST" class="status-form">
                                        @csrf
                                        <input type="hidden" name="terpenuhi" value="{{ $item->terpenuhi ? 0 : 1 }}">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input switch-toggle"
                                                id="switch{{ $item->id }}" name="terpenuhi_toggle"
                                                {{ $item->terpenuhi ? 'checked' : '' }} onchange="this.form.submit()">
                                            <label class="custom-control-label" for="switch{{ $item->id }}"></label>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm editButton"
                                        data-id="{{ $item->id }}"
                                        data-id_member="{{ $item->member->id }}" 
                                        data-nama_barang="{{ $item->nama_barang }}"
                                        data-tanggal_pengajuan="{{ $item->tanggal_pengajuan }}"
                                        data-jumlah="{{ $item->jumlah }}"
                                        data-nama_pelanggan="{{ $item->member->nama_pelanggan }}"
                                        >
                                        Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm deleteButton"
                                    data-id="{{ $item->id }}"
                                    data-nama_pelanggan="{{ $item->member->nama_pelanggan }}">
                                    Hapus
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
<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Tambah Data Pengajuan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="itemForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" id="itemId" name="id">
                <input type="hidden" id="id_member" name="id_member"> 
                <input type="hidden" id="formMode" name="formMode" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="search_member">Nama Pengaju</label>
                        <input type="text" id="search_member" class="form-control" placeholder="Cari nama...">
                        <ul id="member_list" class="list-group position-absolute w-100 d-none"></ul>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_pengajuan">Tanggal Pengajuan</label>
                        <input type="date" class="form-control" id="tanggal_pengajuan" name="tanggal_pengajuan" required>
                    </div>
                    <div class="form-group">
                        <label for="jumlah">Jumlah</label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus pengajuan <strong id="deleteNama"></strong>?
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    // Tambah Barang
    document.getElementById('addButton').addEventListener('click', function () {
        resetForm();
        document.getElementById('itemForm').action = "{{ route('admin.pengajuan.store') }}";
        document.getElementById('methodField').value = 'POST';
        document.getElementById('itemModalLabel').innerText = 'Tambah Pengajuan';
        document.getElementById('formMode').value = 'add';
        $('#itemModal').modal('show');
    });

    document.querySelectorAll('.deleteButton').forEach(button => {
    button.addEventListener('click', function () {
        const itemId = this.dataset.id;
        const itemNama = this.dataset.nama_pelanggan;

        document.getElementById('deleteNama').innerText = itemNama;
        document.getElementById('deleteForm').action = `/admin/pengajuan/${itemId}`;

        $('#deleteModal').modal('show');
    });
});

    $(document).ready(function () {
        $("#search_member").on("input", function () {
            if (document.getElementById('formMode').value === 'edit') {
                return;
            }

            let search = $(this).val();
            let list = $("#member_list");

            if (search.length > 1) {
                $.ajax({
                    url: "{{ route('admin.pengajuan.search-member') }}",
                    type: "GET",
                    data: { search: search },
                    success: function (data) {
                        list.empty().removeClass("d-none");

                        if (data.length > 0) {
                            data.forEach(member => {
                                let item = `<li class="list-group-item list-group-item-action" data-id="${member.id}">${member.nama_pelanggan}</li>`;
                                list.append(item);
                            });
                        } else {
                            list.append(`<li class="list-group-item text-muted">Nama tidak ditemukan</li>`);
                        }
                    },
                    error: function () {
                        list.addClass("d-none");
                    }
                });
            } else {
                list.addClass("d-none");
            }
        });

        $(document).on("click", "#member_list li", function () {
            $("#search_member").val($(this).text());
            $("#id_member").val($(this).data("id"));
            $("#member_list").addClass("d-none");
        });

        $(document).click(function (event) {
            if (!$(event.target).closest("#search_member, #member_list").length) {
                $("#member_list").addClass("d-none");
            }
        });

        // Initialize DataTables
        $('#dataTable').DataTable();
    });

    // Edit Barang
    document.querySelectorAll('.editButton').forEach(button => {
    button.addEventListener('click', function () {
        resetForm();
        const item = this.dataset;

        document.getElementById('itemId').value = item.id;
        document.getElementById('id_member').value = item.id_member;
        document.getElementById('nama_barang').value = item.nama_barang;
        document.getElementById('tanggal_pengajuan').value = item.tanggal_pengajuan;
        document.getElementById('jumlah').value = item.jumlah;
        document.getElementById('search_member').value = item.nama_pelanggan;
        
        // Set field search_member jadi readonly
        document.getElementById('search_member').setAttribute('readonly', 'readonly');

        document.getElementById('itemForm').action = `/admin/pengajuan/${item.id}`;
        document.getElementById('methodField').value = 'POST';
        document.getElementById('itemModalLabel').innerText = 'Edit Pengajuan';
        document.getElementById('formMode').value = 'edit';
        $('#itemModal').modal('show');
    });
});


    // Reset Form
    function resetForm() {
    document.getElementById('itemForm').reset();
    document.getElementById('methodField').value = 'POST';
    document.getElementById('itemId').value = '';
    document.getElementById('formMode').value = 'add';

    // Hapus readonly ketika menambah data baru
    document.getElementById('search_member').removeAttribute('readonly');
}


    // Set readonly for member field in edit mode
    $('#itemModal').on('shown.bs.modal', function () {
    if (document.getElementById('formMode').value === 'edit') {
        document.getElementById('search_member').setAttribute('readonly', 'readonly');
        document.getElementById('search_member').addEventListener('focus', function () {
            this.removeAttribute('readonly');
        });
    }
});
</script>

@endsection
