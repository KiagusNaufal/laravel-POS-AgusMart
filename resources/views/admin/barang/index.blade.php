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
                <h6 class="m-0 font-weight-bold text-primary mb-2">Data barang</h6>
                <!-- Button trigger modal for Add -->
                <button type="button" class="btn btn-primary" id="addButton">
                    Tambah barang
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

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Gambar Barang</th>
                            <th>Harga Beli</th>
                            <th>Persentase Keuntungan</th>
                            <th>Stok</th>
                            <th>Ditarik</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barang as $item)
                        <tr>
                            <td>{{ ($barang->currentPage() - 1) * $barang->perPage() + $loop->iteration }}</td>
                            <td>{{ $item->kode_barang }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->kategori->nama_kategori ?? 'tidak ada kategori' }}</td>
                            <td>
                                <img src="{{ asset('images/' . $item->gambar_barang) }}" alt="{{ $item->nama_barang }}" width="50">
                            </td>
                            <td>{{ $item->harga_beli }}</td>
                            <td>{{ $item->persentase_keuntungan }}%</td>
                            <td>{{ $item->stok }}</td>
                                <td>{{ $item->ditarik == 1 ? 'Iya' : 'Tidak' }}</td>
                            <td>
                                <button class="btn btn-primary btn-sm editButton"
                                    data-id="{{ $item->id }}"
                                    data-kode_barang="{{ $item->kode_barang }}"
                                    data-nama_barang="{{ $item->nama_barang }}"
                                    data-kategori="{{ $item->kategori->id ?? null}}"
                                    data-gambar_barang="{{ $item->gambar_barang }}"
                                    data-harga_beli="{{ $item->harga_beli }}"
                                    data-persentase_keuntungan="{{ $item->persentase_keuntungan }}"
                                    data-stok="{{ $item->stok }}"
                                    data-ditarik="{{ $item->ditarik }}">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $barang->links() }}
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Tambah Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="itemForm" method="POST" action="{{ route('admin.barang.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" id="itemId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kode_barang">Kode Barang</label>
                        <input type="text" class="form-control" id="kode_barang" name="kode_barang" required>
                    </div>
                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                    </div>
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select class="form-control" id="kategori" name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="gambar_barang">Gambar Barang</label>
                        <input type="file" class="form-control" id="gambar_barang" name="gambar_barang" onchange="previewImage(event)">
                    </div>
                    <div class="form-group">
                        <label for="preview_gambar_barang">Preview Gambar Barang</label>
                        <img id="preview_gambar_barang" src="#" alt="Preview Gambar Barang" style="display: none; width: 100px; height: auto;">
                    </div>
                    <div class="form-group">
                        <label for="harga_beli">Harga Beli</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="text" class="form-control" id="harga_beli" name="harga_beli" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="persentase_keuntungan">Persentase Keuntungan</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="persentase_keuntungan" name="persentase_keuntungan" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" required>
                    </div>
                    <div class="form-group">
                        <label for="ditarik">Ditarik</label>
                        <select class="form-control" id="ditarik" name="ditarik" required>
                            <option value="0">Tidak</option>
                            <option value="1">Ya</option>
                        </select>
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

<script>
    // Preview Image
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('preview_gambar_barang');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Tambah Barang
    document.getElementById('addButton').addEventListener('click', function () {
        resetForm();
        document.getElementById('itemForm').action = "{{ route('admin.barang.store') }}";
        document.getElementById('methodField').value = 'POST';
        document.getElementById('itemModalLabel').innerText = 'Tambah barang';
        $('#itemModal').modal('show');
    });

    // Edit Barang
    document.querySelectorAll('.editButton').forEach(button => {
        button.addEventListener('click', function () {
            resetForm();
            const item = this.dataset;

            document.getElementById('itemId').value = item.id;
            document.getElementById('kode_barang').value = item.kode_barang;
            document.getElementById('nama_barang').value = item.nama_barang;
            document.getElementById('kategori').value = item.kategori;
            document.getElementById('gambar_barang').value = item.gambar_barang;
            document.getElementById('preview_gambar_barang').src = "{{ asset('images') }}/" + item.gambar_barang;
            document.getElementById('preview_gambar_barang').style.display = 'block';
            document.getElementById('persentase_keuntungan').value = item.persentase_keuntungan;
            document.getElementById('stok').value = item.stok;
            document.getElementById('ditarik').value = item.ditarik;

            document.getElementById('itemForm').action = `/admin/barang/${item.id}`;
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('itemModalLabel').innerText = 'Edit barang';
            $('#itemModal').modal('show');
        });
    });

    // Reset Form
    function resetForm() {
        document.getElementById('itemForm').reset();
        document.getElementById('methodField').value = 'POST';
        document.getElementById('itemId').value = '';
        document.getElementById('preview_gambar_barang').style.display = 'none';
    }
</script>
@endsection
