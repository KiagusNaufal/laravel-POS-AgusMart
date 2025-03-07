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
                <h6 class="m-0 font-weight-bold text-primary mb-2">Data Pembelian Barang</h6>
                <!-- Button trigger modal for Add -->
                <a href="{{ route('admin.pembelian.create') }}" class="btn btn-primary" id="addButton">
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

                                    </form>
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
@endsection