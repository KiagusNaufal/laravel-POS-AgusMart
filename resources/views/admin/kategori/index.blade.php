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
                <h6 class="m-0 font-weight-bold text-primary mb-2">Data Kategori Barang</h6>
                <!-- Button trigger modal for Add -->
                <button type="button" class="btn btn-primary" id="addButton">
                    Tambah Kategori Barang
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
                <button type="button" class="btn btn-warning btn-block mt-2" id="importButton">
                    Import Kategori
                </button>
                <form method="GET" action="{{ route('kategori.pdf') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-block mt-2">Download PDF</button>
                </form>
                <form method="GET" action="{{ route('kategori.excel') }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block mt-2">Download Excel</button>
                </form>
                <div class="table-responsive p-3">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kategori as $item)
                            <tr>
        <td>{{ ($kategori->currentPage() - 1) * $kategori->perPage() + $loop->iteration }}</td>
                                <td>{{ $item->nama_kategori }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm editButton"
                                        data-id="{{ $item->id }}"
                                        data-nama_kategori="{{ $item->nama_kategori }}" >
                                        Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm deleteButton"
                                    data-id="{{ $item->id }}"
                                    data-nama="{{ $item->nama_kategori }}">
                                    Hapus
                                </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>  
                <div class="d-flex justify-content-center">
                    {{ $kategori->withQueryString()->links() }}

                </div>
            </div>
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
                Apakah Anda yakin ingin menghapus Kategori <strong id="deleteNama"></strong>?
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

<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Kategori Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="importForm" method="POST" action="{{ route('kategori.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Pilih File Excel</label>
                        <input type="file" class="form-control-file" id="file" name="file" required accept=".xlsx, .xls, .csv">
                        <small class="form-text text-muted">
                            Format file harus Excel (.xlsx, .xls) atau CSV. 
                            <a href="{{ asset('templates/kategori_template.xlsx') }}" download>Download template</a>.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Tambah Kategori Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="itemForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" id="itemId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_kategori">Jenis Barang</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" required>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>




<script>

document.getElementById('importButton').addEventListener('click', function () {
        $('#importModal').modal('show');
    });
    // Tambah Barang
    document.getElementById('addButton').addEventListener('click', function () {
        resetForm();
        document.getElementById('itemForm').action = "{{ route('kategori.store') }}";
        document.getElementById('methodField').value = 'POST';
        document.getElementById('itemModalLabel').innerText = 'Tambah Jenis Barang';
        $('#itemModal').modal('show');
    });
    document.querySelectorAll('.deleteButton').forEach(button => {
    button.addEventListener('click', function () {
        const itemId = this.dataset.id;
        const itemNama = this.dataset.nama;

        document.getElementById('deleteNama').innerText = itemNama;
        document.getElementById('deleteForm').action = `/admin/kategori/${itemId}`;

        $('#deleteModal').modal('show');
    });
});

    // Edit Barang
    document.querySelectorAll('.editButton').forEach(button => {
        button.addEventListener('click', function () {
            resetForm();
            const item = this.dataset;

            document.getElementById('itemId').value = item.id;
            document.getElementById('nama_kategori').value = item.nama_kategori;


            document.getElementById('itemForm').action = `/admin/kategori/${item.id}`;
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('itemModalLabel').innerText = 'Edit Barang';
            $('#itemModal').modal('show');
        });
    });

    // Reset Form
    function resetForm() {
        document.getElementById('itemForm').reset();
        document.getElementById('methodField').value = 'POST';
        document.getElementById('itemId').value = '';
    }
</script>
@endsection

