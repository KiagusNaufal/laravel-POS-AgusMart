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
                <form method="GET" action="{{ route('admin.laporan.barang') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filter">Cari Barang</label>
                            <input type="text" class="form-control" id="filter" name="filter" placeholder="Masukkan nama atau kode barang" value="{{ request('filter') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="kategori">Kategori</label>
                            <select class="form-control" id="kategori" name="kategori">
                                <option value="">Semua Kategori</option>
                                @foreach($kategori as $kat)
                                    <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="ditarik">Ditarik</label>
                            <select class="form-control" id="ditarik" name="ditarik">
                                <option value="">Status Ditarik</option>
                                <option value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive p-3">
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barang as $item)
                        <tr>
                            <td>{{ ($barang->currentPage() - 1) * $barang->perPage() + $loop->iteration }}</td>
                            <td>{{ $item->kode_barang }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->kategori->nama_kategori }}</td>
                            <td>
                                <img src="{{ asset('images/' . $item->gambar_barang) }}" alt="{{ $item->nama_barang }}" width="50">
                            </td>
                            <td>{{ $item->harga_beli }}</td>
                            <td>{{ $item->persentase_keuntungan }}%</td>
                            <td>{{ $item->stok }}</td>
                            <td>{{ $item->ditarik == 1 ? 'Iya' : 'Tidak' }}</td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-center">
    {{ $barang->appends(request()->query())->links() }}
</div>

        </div>
    </div>
</div>

@endsection
