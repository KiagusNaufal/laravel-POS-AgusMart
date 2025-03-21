@extends('layouts.supervisor')

@section('main-content2')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="row justify-content-between mb-4">
    <!-- Total Penjualan -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Penjualan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($paginatedBarang->sum('total_penjualan'), 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Keuntungan -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Keuntungan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($paginatedBarang->sum('keuntungan'), 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Terjual -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Terjual</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $paginatedBarang->sum('total_terjual') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Barang</h6>
            </div>
            

            <div class="card-body">
                <form method="GET" action="{{ route('super.laporan.barang') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="search_term">Cari Barang</label>
                            <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Masukkan nama atau kode barang" value="{{ request('filter') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="end_date">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>
                <form method="GET" action="{{ route('super.laporan.barang.pdf') }}">
                    @csrf
                    <input type="hidden" name="search_term" value="{{ request('search_term') }}">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <button type="submit" class="btn btn-danger btn-block mt-2">Download PDF</button>
                </form>
                <form method="GET" action="{{ route('super.laporan.barang.excel') }}">
                    @csrf
                    <input type="hidden" name="search_term" value="{{ request('search_term') }}">
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
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Total Penjualan</th>
                            <th>Keuntungan</th>
                            <th>Total Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedBarang as $item)
                        <tr>
                            <td>{{ $loop->iteration + $paginatedBarang->firstItem() - 1 }}</td>
                            <td>{{ $item['kode_barang'] }}</td>
                            <td>{{ $item['nama_barang'] }}</td>
                            <td>Rp {{ number_format($item['total_penjualan'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item['keuntungan'], 0, ',', '.') }}</td>
                            <td>{{ $item['total_terjual'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $paginatedBarang->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

@endsection