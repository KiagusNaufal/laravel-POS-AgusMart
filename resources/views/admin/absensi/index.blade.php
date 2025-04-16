@extends('layouts.admin')

@section('main-content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-4">
                <h6 class="m-0 font-weight-bold text-primary mb-2">Data Absensi Kerja</h6>
                <button type="button" class="btn btn-primary" id="addButton">
                    Tambah Absensi
                </button>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger border-left-danger">
                <ul class="pl-4 my-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="card-body">
                <button type="button" class="btn btn-warning btn-block mt-2" id="importButton">
                    Import Absensi
                </button>
                <form method="GET" action="{{ route('admin.absensi.pdf') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-block mt-2">Download PDF</button>
                </form>
                <form method="GET" action="{{ route('admin.absensi.excel') }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block mt-2">Download Excel</button>
                </form>
                

                <div class="table-responsive">
                    <table class="table table-bordered" id="absensiTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>Tanggal Masuk</th>
                                <th>Waktu Masuk</th>
                                <th>Status</th>
                                <th>Waktu Pulang</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($absensi as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->user->name ?? '-' }}</td>
                                <td>{{ $item->tanggal_masuk->format('Y-m-d') }}</td>
                                <td>{{ $item->waktu_masuk->format('H:i') }}</td>
                                <td>
                                    <select class="form-control form-control-sm status-select" data-id="{{ $item->id }}">
                                        <option value="Masuk" {{ $item->status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                        <option value="Cuti" {{ $item->status == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                        <option value="Sakit" {{ $item->status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                    </select>
                                    <td id="waktu-pulang-{{ $item->id }}">
                                        @if ($item->status == 'Masuk')
                                            @if (empty($item->waktu_akhir_kerja) || $item->waktu_akhir_kerja == '00:00:00')
                                                <button class="btn btn-success btn-sm selesaiButton" 
                                                        data-id="{{ $item->id }}" 
                                                        data-nama="{{ $item->user->name ?? '-' }}">
                                                    Selesai
                                                </button>
                                            @else
                                                {{ \Carbon\Carbon::parse($item->waktu_akhir_kerja)->format('H:i') }}
                                            @endif
                                        @else
                                            {{ $item->waktu_akhir_kerja && $item->waktu_akhir_kerja != '00:00:00' ? \Carbon\Carbon::parse($item->waktu_akhir_kerja)->format('H:i') : '00:00' }}
                                        @endif
                                    </td>
                                <td>
                                    <button class="btn btn-primary btn-sm editButton"
                                        data-id="{{ $item->id }}"
                                        data-user_id="{{ $item->user_id }}"
                                        data-tanggal_masuk="{{ $item->tanggal_masuk->format('Y-m-d') }}"
                                        data-waktu_masuk="{{ $item->waktu_masuk->format('H:i') }}"
                                        data-waktu_akhir_kerja="{{ $item->waktu_akhir_kerja ? $item->waktu_akhir_kerja->format('H:i') : '' }}"
                                        data-status="{{ $item->status }}">
                                        Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm deleteButton"
                                        data-id="{{ $item->id }}"
                                        data-nama="{{ $item->user->name ?? '-' }}">
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

<!-- Modal Hapus -->
<!-- Modal Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Yakin ingin menghapus data absensi <strong id="deleteNama"></strong>?
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

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="importForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data Absensi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="importFile">Pilih File Import (CSV / Excel)</label>
                        <input type="file" name="file" id="importFile" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="fileFormat">Unduh Format File</label>
                        <a href="{{ route('absensi.format') }}" class="btn btn-info btn-sm" target="_blank">Unduh Format</a>
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


<!-- Modal Tambah/Edit -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="itemForm" method="POST">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="PUT">
                <input type="hidden" id="absensiId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalLabel">Tambah Absensi</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Pegawai</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Waktu Masuk</label>
                        <input type="time" name="waktu_masuk" id="waktu_masuk" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Waktu Pulang</label>
                        <input type="time" name="waktu_akhir_kerja" id="waktu_akhir_kerja" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Masuk">Masuk</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Sakit">Sakit</option>
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

<!-- DataTables CSS and JS -->
<script>
$(document).ready(function() {
    $('#absensiTable').DataTable({
        'searching': true,
        dom: '<"top"lf>rt<"bottom"ipB>',    
        responsive: true,
        language: {
             url: 'public/js/datatables-id.json'
        },
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, // Nama Pegawai
            { responsivePriority: 2, targets: 2 }, // Tanggal Masuk
            { orderable: false, targets: [6] } // Disable sorting for action column
        ]
    });
});

$('.status-select').change(function () {
    const select = $(this);
    const id = select.data('id');
    const newStatus = select.val();

    $.ajax({
        url: '/admin/absensi/update-status/' + id,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: newStatus
        },
        success: function(response) {
            const cell = $(`#waktu-pulang-${id}`);
            
            if (response.status === 'Masuk') {
                if (!response.waktu_akhir_kerja || response.waktu_akhir_kerja === '00:00:00') {
                    cell.html(`
                        <button class="btn btn-success btn-sm selesaiButton" 
                                data-id="${id}" 
                                data-nama="${response.nama}">
                            Selesai
                        </button>
                    `);
                } else {
                    cell.html(formatTime(response.waktu_akhir_kerja));
                }
            } else {
                cell.html('00:00');
            }
            
            showAlert('success', response.message);
        },
        error: function(xhr) {
            showAlert('danger', xhr.responseJSON.message);
            select.val(select.data('current-value'));
        }
    });
});
// Import button click handler
$('#importButton').click(function() {
    $('#importForm').attr('action', '{{ route("absensi.import") }}');
    $('#importModal').modal('show');
});

// Handle import form submission
$('#importForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#importModal').modal('hide');
            showAlert('success', response.message || 'Import berhasil');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        },
        error: function(xhr) {
            const errors = xhr.responseJSON.errors;
            let errorMessage = xhr.responseJSON.message || 'Terjadi kesalahan saat import';
            
            if (errors && errors.file) {
                errorMessage = errors.file[0];
            }
            
            showAlert('danger', errorMessage);
        }
    });
});

// Fungsi helper untuk format waktu
function formatTime(timeString) {
    if (!timeString) return '-';
    const time = new Date(`2000-01-01 ${timeString}`);
    return time.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', hour12: false});
}
$('.selesaiButton').click(function () {
    const id = $(this).data('id');
    const nama = $(this).data('nama');
    
    $.ajax({
        url: '/admin/absensi/selesai/' + id,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // Tampilkan alert sukses
                showAlert('success', response.message);
                
                // Refresh halaman setelah 1 detik
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('success', response.message || 'Gagal memperbarui status');
            }
        },
        error: function(xhr) {
            showAlert('danger', xhr.responseJSON?.message || 'Gagal memperbarui status');
        }
    });
});
$('#addButton').click(function () {
    $('#itemModalLabel').text('Tambah Absensi');
    $('#itemForm').trigger("reset");
    $('#itemForm').attr('action', '/admin/absensi/store');
    $('#methodField').val('POST'); // ini penting!
    $('#itemModal').modal('show');
});


        $('.editButton').click(function () {
    const id = $(this).data('id');
    const userId = $(this).data('user_id');
    const tanggalMasuk = $(this).data('tanggal_masuk');
    const waktuMasuk = $(this).data('waktu_masuk');
    const waktuAkhirKerja = $(this).data('waktu_akhir_kerja');
    const status = $(this).data('status');
    
    $('#absensiId').val(id);
    $('#user_id').val(userId);
    $('#tanggal_masuk').val(tanggalMasuk);
    $('#waktu_masuk').val(waktuMasuk);
    $('#waktu_akhir_kerja').val(waktuAkhirKerja);
    $('#status').val(status);
    
    // Set the form action to the update route
    $('#itemForm').attr('action', '/admin/absensi/' + id);
    $('#methodField').val('PUT');  // This will be used to spoof the PUT method
    $('#itemModalLabel').text('Edit Absensi');
    
    $('#itemModal').modal('show');
});


        // Delete button click handler
        $('.deleteButton').click(function () {
    const id = $(this).data('id');
    const nama = $(this).data('nama');
    $('#deleteNama').text(nama);
    
    // Set action URL untuk form delete
    $('#deleteForm').attr('action', '/admin/absensi/' + id);
    $('#deleteModal').modal('show');
});


        function resetForm() {
            $('#itemForm')[0].reset();
            $('#methodField').val('POST');
            $('#absensiId').val('');
        }

        // Form submission handling
        $('#itemForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const url = form.attr('action');
    const method = $('#methodField').val();

    // Create FormData object for proper form submission
    const formData = new FormData(this);

    // Add the _method field for spoofing PUT/PATCH/DELETE
    if (method !== 'POST') {
        formData.append('_method', method);
    }

    $.ajax({
        url: url,
        type: 'POST', // Always use POST and spoof the method
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#itemModal').modal('hide');
            showAlert('success', response.message || 'Operasi berhasil');
            // Reload page to see changes
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        },
        error: function(xhr) {
    let errorMessage = 'Terjadi kesalahan';
    
    // Check if response is JSON
    if (xhr.responseJSON) {
        errorMessage = xhr.responseJSON.message || 
                      (xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).join('<br>') : 'Terjadi kesalahan');
    } 
    // If not JSON, try to get the response text
    else if (xhr.responseText) {
        errorMessage = xhr.responseText;
    }
    
    showAlert('danger', errorMessage);
}
    });
});
        $('#deleteForm').on('submit', function(e) {
    e.preventDefault();  // Prevent the form from submitting normally
    
    const form = $(this);
    const url = form.attr('action');  // Get the form action URL
    
    $.ajax({
        url: url,
        type: 'POST',  // Use POST and spoof the DELETE method
        data: {
            _token: $('input[name="_token"]').val(),  // CSRF token
            _method: 'DELETE'  // Spoofing DELETE method
        },
        success: function(response) {
            // Hide the delete modal
            $('#deleteModal').modal('hide');
            
            // Show success alert
            showAlert('success', response.message || 'Data berhasil dihapus');
            
            // Reload DataTable after 1.5 seconds
            setTimeout(() => {
                // Ensure DataTable is initialized with AJAX configuration
                $('#absensiTable').DataTable().ajax.reload(null, false);
            }, 1500);
        },
        error: function(xhr) {
            // If an error occurs, show an error alert
            let errorMessage = xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus';
            showAlert('danger', errorMessage);
            
            // Log the entire error object to the console for debugging
            console.error(xhr);
        }
    });
});


// Fungsi untuk menampilkan alert
function showAlert(type, message) {
    // Hapus alert sebelumnya jika ada
    $('.alert-dismissible').alert('close');
    
    // Buat alert baru dengan class yang benar
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Tempatkan alert setelah card header
    $('.card-header').after(alertHtml);
    
    // Auto-remove alert after 5 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
}
</script>

@endsection
