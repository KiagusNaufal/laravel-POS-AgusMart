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
                <h6 class="m-0 font-weight-bold text-primary mb-2">Data Member</h6>
                <!-- Button trigger modal for Add -->
                <button type="button" class="btn btn-primary" id="addButton">
                    Tambah Member
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
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Pelanggan</th>
                                <th>Nama Pelanggan</th>
                                <th>No. Telp</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->kode_pelanggan }}</td>
                                <td>{{ $item->nama_pelanggan }}</td>
                                <td>{{ $item->no_telp }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm editButton"
                                        data-id="{{ $item->id }}"
                                        data-kode_pelanggan="{{ $item->kode_pelanggan }}"
                                        data-nama_pelanggan="{{ $item->nama_pelanggan }}"
                                        data-no_telp="{{ $item->no_telp }}"
                                        data-email="{{ $item->email }}"
                                        >
                                        Edit
                                    </button>
                                    {{-- <form action="{{ route('jenis-barang.delete', ['id' => $item->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form> --}}
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
                <h5 class="modal-title" id="itemModalLabel">Tambah Pemasok Barang</h5>
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
                        <label for="nama_pelanggan">Nama Pelanggan</label>
                        <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan" required>
                    </div>
                    <div class="form-group">
                    <div class="form-group">
                        <label for="no_telp">No. Telp</label>
                        <input type="number" class="form-control" id="no_telp" name="no_telp" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
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
    // Tambah Barang
    document.getElementById('addButton').addEventListener('click', function () {
        resetForm();
        document.getElementById('itemForm').action = "{{ route('admin.member.store') }}";
        document.getElementById('methodField').value = 'POST';
        document.getElementById('itemModalLabel').innerText = 'Tambah Member';
        $('#itemModal').modal('show');
    });

    // Edit Barang
    document.querySelectorAll('.editButton').forEach(button => {
        button.addEventListener('click', function () {
            resetForm();
            const item = this.dataset;

            document.getElementById('itemId').value = item.id;
            document.getElementById('nama_pelanggan').value = item.nama_pelanggan;
            document.getElementById('no_telp').value = item.no_telp;
            document.getElementById('email').value = item.email;


            document.getElementById('itemForm').action = `/admin/member/${item.id}`;
            document.getElementById('methodField').value = 'POST';
            document.getElementById('itemModalLabel').innerText = 'Edit Member';
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

