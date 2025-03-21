@extends('layouts.admin')

@section('main-content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ __('Dashboard') }}</h1>

    @if (session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success border-left-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="row">
        <!-- Earnings (Monthly) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pendapatan Bulanan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($pendapatanBulanan, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Earnings (Annual) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pendapatan Tahunan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($pendapatanTahunan, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Total Barang Terjual (Bulanan) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Barang Terjual Bulan Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalBarangTerjualBulan, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Total Barang Terjual (Tahunan) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Barang Terjual Tahun Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalBarangTerjualTahun, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
        <!-- Chart -->
        <div class="row">
            <div class="col-12"> <!-- Gunakan col-12 supaya full width -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Grafik Pendapatan Bulanan</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin') }}" class="form-inline mb-4">
                            <label for="bulan" class="mr-2">Pilih Bulan:</label>
                            <select name="bulan" id="bulan" class="form-control" onchange="this.form.submit()">
                                @foreach ($bulanList as $key => $bulan)
                                    <option value="{{ $key }}" {{ $selectedMonth == $key ? 'selected' : '' }}>
                                        {{ $bulan }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        <div class="chart-area w-100"> <!-- Tambahkan w-100 -->
                            <canvas id="myAreaChart" style="width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script>
        // Set new default font family and font color to mimic Bootstrap's default styling
        Chart.defaults.font.family = 'Nunito, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        Chart.defaults.color = '#858796';

function number_format(number, decimals, dec_point, thousands_sep) {
  // *     example: number_format(1234.56, 2, ',', ' ');
  // *     return: '1 234,56'
  number = (number + '').replace(',', '').replace(' ', '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

var ctx = document.getElementById("myAreaChart");

// Data dari PHP
 // Data dari PHP (Blade)
 var labels = @json($labels);
    var dataPendapatan = @json($data); // Total Pendapatan
    var dataTransaksi = @json($dataTransaksi); // Jumlah Transaksi

    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Jumlah Transaksi",
                    data: dataTransaksi,
                    borderColor: "red",
                    backgroundColor: "rgba(255, 99, 132, 0.5)",
                    yAxisID: 'y1',
                    pointStyle: 'circle',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 2,
                },
                {
                    label: "Total Pendapatan",
                    data: dataPendapatan,
                    borderColor: "blue",
                    backgroundColor: "rgba(54, 162, 235, 0.5)",
                    yAxisID: 'y',
                    pointStyle: 'circle',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 2,
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 12
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: "Pendapatan (Rp)"
                    },
                    min: 0,
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value) {
                            return 'Rp ' + number_format(value);
                        }
                    },
                    grid: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                },
                y1: {
                    title: {
                        display: true,
                        text: "Jumlah Transaksi"
                    },
                    position: "right",
                    min: 0,
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        usePointStyle: true,
                        boxWidth: 15,
                    },
                    onClick: (e, legendItem, legend) => {
                        const index = legendItem.datasetIndex;
                        const ci = legend.chart;
                        const meta = ci.getDatasetMeta(index);
                        meta.hidden = meta.hidden === null ? !ci.data.datasets[index].hidden : null;
                        ci.update();
                    }
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFont: { size: 14 },
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem) {
                            let value = tooltipItem.raw;
                            if (tooltipItem.datasetIndex === 0) {
                                return `Jumlah Transaksi: ${value}`;
                            } else {
                                return `Pendapatan: Rp ${number_format(value)}`;
                            }
                        }
                    }
                }
            }
        }
    });
function number_format(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}
</script>
@endsection
