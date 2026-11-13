@extends('layouts.app')

@section('title', 'แดชบอร์ดภาพรวมสต็อกหมึก')

@section('content')
<div class="row g-3 mb-4">
    <!-- Stat 1: Total Inks -->
    <div class="col-6 col-lg-3">
        <div class="card h-100 p-3 bg-white border-start border-primary border-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">รายการหมึกทั้งหมด</span>
                    <h3 class="fw-bold mb-0 mt-1">{{ number_format($totalInks) }}</h3>
                    <small class="text-muted">ชนิด/รุ่น ในระบบ</small>
                </div>
                <div class="stat-card icon-box bg-primary-subtle text-primary">
                    <i class="bi bi-printer"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 2: Total Units in Stock -->
    <div class="col-6 col-lg-3">
        <div class="card h-100 p-3 bg-white border-start border-success border-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">ยอดคงเหลือรวม</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($totalStockUnits) }}</h3>
                    <small class="text-muted">ตลับ / ขวด พร้อมใช้งาน</small>
                </div>
                <div class="stat-card icon-box bg-success-subtle text-success">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 3: Low Stock Alert -->
    <div class="col-6 col-lg-3">
        <div class="card h-100 p-3 bg-white border-start border-warning border-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">หมึกใกล้หมด (เตือน)</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1">{{ number_format($lowStockCount) }}</h3>
                    <small class="text-muted">&le; จุดสั่งซื้อขั้นต่ำ</small>
                </div>
                <div class="stat-card icon-box bg-warning-subtle text-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 4: Out of Stock -->
    <div class="col-6 col-lg-3">
        <div class="card h-100 p-3 bg-white border-start border-danger border-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">หมึกหมดสต็อก</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1">{{ number_format($outOfStockCount) }}</h3>
                    <small class="text-muted">ต้องสั่งซื้อด่วน (0 หน่วย)</small>
                </div>
                <div class="stat-card icon-box bg-danger-subtle text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Performance Banner -->
<div class="card bg-white p-3 p-md-4 mb-4 shadow-sm">
    <div class="row align-items-center g-3">
        <div class="col-lg-6">
            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-calendar2-week text-primary"></i>
                สรุปยอดความเคลื่อนไหวประจำเดือน {{ \Carbon\Carbon::now()->locale('th')->translatedFormat('F Y') }}
            </h5>
            @auth
                <p class="text-muted small mb-0">ยินดีต้อนรับคุณ <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role_label }})</p>
            @else
                <p class="text-muted small mb-0">พนักงานทุกคนสามารถตรวจสอบสต็อกและขอเบิกหมึกไปใช้งานได้ทันทีโดยไม่ต้องเข้าสู่ระบบ</p>
            @endauth
        </div>
        <div class="col-lg-6 d-flex flex-wrap justify-content-lg-end gap-3">
            <div class="bg-light px-3 py-2 rounded-3 border d-flex align-items-center gap-2">
                <i class="bi bi-arrow-down-circle text-success fs-4"></i>
                <div>
                    <div class="text-muted small">รับเข้าเดือนนี้</div>
                    <div class="fw-bold text-success fs-6">+{{ number_format($monthlyStockIn) }} หน่วย</div>
                </div>
            </div>
            <div class="bg-light px-3 py-2 rounded-3 border d-flex align-items-center gap-2">
                <i class="bi bi-arrow-up-circle text-primary fs-4"></i>
                <div>
                    <div class="text-muted small">เบิกจ่ายเดือนนี้</div>
                    <div class="fw-bold text-primary fs-6">-{{ number_format($monthlyStockOut) }} หน่วย</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('transactions.stock_out') }}" class="btn btn-warning text-dark fw-semibold">
                    <i class="bi bi-box-arrow-up-right me-1"></i> เบิกหมึก
                </a>
                @if(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ route('transactions.stock_in') }}" class="btn btn-success fw-semibold">
                        <i class="bi bi-box-arrow-in-down me-1"></i> รับหมึกเข้า
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Chart: Brand Distribution -->
    <div class="col-lg-6">
        <div class="card bg-white p-3 p-md-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-pie-chart text-primary me-1"></i> สต็อกคงเหลือแยกตามยี่ห้อ (Brand)
                </h6>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="brandChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Department Requisitions -->
    <div class="col-lg-6">
        <div class="card bg-white p-3 p-md-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-bar-chart-fill text-success me-1"></i> ปริมาณการเบิกหมึกแยกตามแผนก
                </h6>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock & Recent Transactions Row -->
<div class="row g-4">
    <!-- Low Stock Alert Box -->
    <div class="col-lg-5">
        <div class="card bg-white h-100 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-danger d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    รายการที่ต้องสั่งซื้อ / หมึกใกล้หมด
                </h6>
                <span class="badge bg-danger rounded-pill">{{ $alertInks->count() }} รายการ</span>
            </div>
            <div class="card-body p-0">
                @if($alertInks->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                        ระดับสต็อกหมึกทุกรายการอยู่ในเกณฑ์ปกติ
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($alertInks as $item)
                            <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-color-dot" style="background-color: {{ $item->color_code }}"></span>
                                        <span class="fw-semibold text-dark">{{ $item->model }}</span>
                                        <span class="badge bg-light text-secondary border">{{ $item->brand }}</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $item->name }}</small>
                                    <small class="text-muted">
                                        คงเหลือ: 
                                        <strong class="{{ $item->current_stock <= 0 ? 'text-danger' : 'text-warning' }}">
                                            {{ $item->current_stock }} {{ $item->unit }}
                                        </strong> 
                                        (ขั้นต่ำ: {{ $item->min_stock }} {{ $item->unit }})
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $item->status_badge_class }} mb-1 d-inline-block">
                                        {{ $item->status_label }}
                                    </span>
                                    <div>
                                        @if(auth()->check() && auth()->user()->isAdmin())
                                            <a href="{{ route('transactions.stock_in', ['ink_id' => $item->id]) }}" class="btn btn-sm btn-outline-success py-1 px-2 text-nowrap" style="font-size: 0.78rem;">
                                                <i class="bi bi-plus-circle"></i> รับเข้าสต็อก
                                            </a>
                                        @else
                                            <a href="{{ route('transactions.stock_out', ['ink_id' => $item->id]) }}" class="btn btn-sm btn-outline-warning text-dark py-1 px-2 text-nowrap {{ $item->current_stock <= 0 ? 'disabled' : '' }}" style="font-size: 0.78rem;">
                                                <i class="bi bi-box-arrow-up-right"></i> เบิกหมึก
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="card-footer bg-white border-top text-center py-2">
                <a href="{{ route('inks.index', ['status' => 'low']) }}" class="small text-decoration-none fw-medium text-primary">
                    ดูหมึกใกล้หมดทั้งหมด <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="col-lg-7">
        <div class="card bg-white h-100 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    ประวัติการเบิก-รับหมึกล่าสุด
                </h6>
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.8rem;">
                    ดูทั้งหมด
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>วันที่ / เลขที่</th>
                            <th>ประเภท</th>
                            <th>รุ่นหมึก</th>
                            <th class="text-center">จำนวน</th>
                            <th>ผู้ทำรายการ / แผนก</th>
                            <th class="text-center">สลิป</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $tx)
                            <tr>
                                <td>
                                    <div class="fw-semibold small text-dark">{{ $tx->action_date->format('d/m/Y') }}</div>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $tx->transaction_code }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $tx->type_badge_class }} py-1 px-2">
                                        {{ $tx->type_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge-color-dot" style="background-color: {{ $tx->ink->color_code ?? '#333' }}"></span>
                                        <strong>{{ $tx->ink->model ?? '-' }}</strong>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $tx->ink->brand ?? '' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold {{ $tx->type === 'in' ? 'text-success' : 'text-primary' }}">
                                        {{ $tx->type === 'in' ? '+' : '-' }}{{ $tx->quantity }}
                                    </span>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $tx->ink->unit ?? '' }}</small>
                                </td>
                                <td>
                                    <div class="small fw-medium">{{ $tx->person_name }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ $tx->department_name ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('transactions.show', $tx) }}" class="btn btn-sm btn-light border py-1 px-2" title="ดูสลิป / พิมพ์">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีประวัติการทำรายการ</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Brand Distribution Chart
        const brandData = @json($brandDistribution);
        const brandCtx = document.getElementById('brandChart').getContext('2d');
        new Chart(brandCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(brandData),
                datasets: [{
                    data: Object.values(brandData),
                    backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#6f42c1', '#d63384'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { family: 'Prompt' } }
                    }
                }
            }
        });

        // Department Requisition Chart
        const deptData = @json($deptRequisitions);
        const deptCtx = document.getElementById('deptChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(deptData),
                datasets: [{
                    label: 'จำนวนเบิก (ตลับ/ขวด)',
                    data: Object.values(deptData),
                    backgroundColor: '#0d6efd',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Prompt' } }
                    },
                    x: {
                        ticks: { font: { family: 'Prompt' } }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush
