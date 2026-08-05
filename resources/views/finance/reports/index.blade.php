@extends('layouts.app-bootstrap')

@section('title', 'Dashboard Keuangan')

@section('header')
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <div>
        <h2 class="h3 mb-1 fw-bold">
            <i class="bi bi-graph-up-arrow text-primary me-2"></i>Dashboard Keuangan
        </h2>
        <p class="text-muted mb-0">Ringkasan pendapatan sekolah dan status tagihan siswa</p>
    </div>
</div>
@endsection

@section('content')

{{-- 1. Statistik Global --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="text-muted fw-semibold">Pendapatan Bulan Ini</div>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalReceivedThisMonth, 0, ',', '.') }}</h3>
                </div>
                <div class="small text-muted mt-3">
                    Berdasarkan pembayaran yang diverifikasi.
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="text-muted fw-semibold">Tertagih Bulan Ini</div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalBilledThisMonth, 0, ',', '.') }}</h3>
                </div>
                <div class="small text-muted mt-3">
                    Total target SPP & tagihan baru.
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-danger text-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-semibold opacity-75">Total Tunggakan</div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-exclamation-triangle fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</h3>
                </div>
                <div class="small opacity-75 mt-3">
                    Akumulasi tagihan yang belum lunas.
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning text-dark">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-semibold opacity-75">Perlu Verifikasi</div>
                        <div class="bg-white bg-opacity-50 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $pendingVerifications }} Pembayaran</h3>
                </div>
                <div class="mt-3">
                    <a href="{{ route('finance.payments.index', ['status' => 'pending']) }}" class="btn btn-sm btn-dark rounded-pill">Lihat & Verifikasi</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 2. Chart Pendapatan --}}
<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Grafik Pendapatan (6 Bulan Terakhir)</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradien untuk warna bar
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(25, 135, 84, 0.8)'); // Success color
    gradient.addColorStop(1, 'rgba(25, 135, 84, 0.2)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Total Pendapatan (Rp)',
                data: {!! json_encode($chartValues) !!},
                backgroundColor: gradient,
                borderColor: '#198754',
                borderWidth: 2,
                borderRadius: 8,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw;
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    },
                    grid: {
                        borderDash: [5, 5]
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endsection
