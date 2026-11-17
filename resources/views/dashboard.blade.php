@extends('layouts.app')

@section('title', 'แดชบอร์ดภาพรวมสต็อกหมึก')

@section('content')
<!-- Page Header & Quick Action -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2 text-dark">
            <i class="bi bi-speedometer2 text-primary"></i>
            แดชบอร์ดภาพรวมสต็อกหมึก
        </h4>
        <p class="text-muted small mb-0">
            พนักงานทุกคนสามารถตรวจสอบสต็อกและขอเบิกหมึกไปใช้งานได้ทันที บริษัท บิ๊กล็อต โฮมโปรดักส์ จำกัด
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('transactions.stock_out') }}" class="btn btn-warning text-dark fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-up-right"></i>
            <span>ทำรายการเบิกหมึก</span>
        </a>
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('transactions.stock_in') }}" class="btn btn-success fw-semibold px-3 shadow-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-plus-circle"></i>
                <span>รับหมึกเข้า</span>
            </a>
        @endif
    </div>
</div>

<!-- 4 Key Stat Cards -->
<div class="row g-3 mb-4">
    <!-- Stat 1: Total Stock Units -->
    <div class="col-6 col-lg-3">
        <div class="card p-3 bg-white border-0 shadow-sm h-100 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">หมึกพร้อมใช้งาน</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($totalStockUnits) }}</h3>
                    <small class="text-muted">ตลับ/ขวด ในคลัง</small>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-3">
                    <i class="bi bi-box-seam fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 2: Total Ink Models -->
    <div class="col-6 col-lg-3">
        <div class="card p-3 bg-white border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">ชนิดรุ่นทั้งหมด</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($totalInks) }}</h3>
                    <small class="text-muted">รุ่นหมึกในระบบ</small>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-3">
                    <i class="bi bi-printer fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 3: Low Stock Alert -->
    <div class="col-6 col-lg-3">
        <div class="card p-3 bg-white border-0 shadow-sm h-100 border-start border-4 {{ ($lowStockCount + $outOfStockCount) > 0 ? 'border-danger' : 'border-secondary' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">หมึกใกล้หมด / หมด</span>
                    <h3 class="fw-bold {{ ($lowStockCount + $outOfStockCount) > 0 ? 'text-danger' : 'text-secondary' }} mb-0 mt-1">
                        {{ number_format($lowStockCount + $outOfStockCount) }}
                    </h3>
                    <small class="text-muted">รายการที่ควรเติม</small>
                </div>
                <div class="p-3 {{ ($lowStockCount + $outOfStockCount) > 0 ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary' }} rounded-3">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 4: Monthly Requisitions -->
    <div class="col-6 col-lg-3">
        <div class="card p-3 bg-white border-0 shadow-sm h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-medium">ยอดเบิกเดือนนี้</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($monthlyStockOut) }}</h3>
                    <small class="text-muted">ตลับ/ขวด ที่เบิกไป</small>
                </div>
                <div class="p-3 bg-warning-subtle text-dark rounded-3">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid (Left: Quick Stock & Requisition | Right: Recent History & Alerts) -->
<div class="row g-4">
    <!-- Left Column: Quick Stock List with Instant Search & Requisition -->
    <div class="col-lg-7">
        <div class="card bg-white border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                        ตรวจสอบสต็อก & เบิกหมึก
                    </h6>
                </div>
                <!-- Compact Search Box -->
                <div style="max-width: 260px; width: 100%;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="quickInkSearch" class="form-control bg-light border-start-0" placeholder="พิมพ์ค้นหารุ่น/เครื่องพิมพ์..." autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="quickStockTable">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th>รุ่นหมึก / ยี่ห้อ</th>
                            <th>เครื่องพิมพ์ที่รองรับ</th>
                            <th class="text-center">คงเหลือ</th>
                            <th class="text-end pe-3">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inks as $ink)
                            <tr class="ink-row" data-search="{{ strtolower($ink->code . ' ' . $ink->brand . ' ' . $ink->model . ' ' . $ink->color . ' ' . $ink->compatible_printers) }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-color-dot flex-shrink-0" style="width: 12px; height: 12px; background-color: {{ $ink->color_code ?? '#333' }};"></span>
                                        <div>
                                            <div class="fw-semibold text-dark text-nowrap">
                                                <span class="text-muted small">[{{ $ink->code }}]</span> {{ $ink->model }}
                                            </div>
                                            <small class="text-muted">{{ $ink->brand }} &bull; {{ $ink->color }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 180px;" title="{{ $ink->compatible_printers }}">
                                        {{ $ink->compatible_printers ?: 'มาตรฐานทั่วไป' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($ink->current_stock <= 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">หมด (0)</span>
                                    @elseif($ink->current_stock <= $ink->min_stock)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $ink->current_stock }} {{ $ink->unit }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $ink->current_stock }} {{ $ink->unit }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if($ink->current_stock > 0)
                                        <a href="{{ route('transactions.stock_out', ['ink_id' => $ink->id]) }}" class="btn btn-sm btn-outline-warning text-dark fw-bold py-1 px-2" title="เบิกรุ่นนี้">
                                            เบิก <i class="bi bi-chevron-right small"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-light border py-1 px-2 text-muted" disabled>
                                            หมด
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                    ยังไม่มีรายการหมึกพิมพ์ในระบบ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top py-2 d-flex justify-content-between align-items-center">
                <small class="text-muted">ทั้งหมด {{ $inks->count() }} รุ่นหมึก</small>
                <a href="{{ route('inks.index') }}" class="small text-decoration-none text-primary fw-medium">
                    ดูคลังหมึกพิมพ์ทั้งหมด <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Transactions & Low Stock Alerts -->
    <div class="col-lg-5">
        <div class="d-flex flex-column gap-3 h-100">
            <!-- Low Stock Quick Alert Box (Only shows if there are items needing reorder) -->
            @if($alertInks->isNotEmpty())
                <div class="card border-0 shadow-sm bg-danger-subtle border-start border-4 border-danger p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-bold text-danger d-flex align-items-center gap-2 small">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            หมึกใกล้หมด / ต้องสั่งซื้อด่วน ({{ $alertInks->count() }} รุ่น)
                        </div>
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <a href="{{ route('transactions.stock_in') }}" class="btn btn-sm btn-danger py-0 px-2 fw-semibold" style="font-size: 0.75rem;">
                                + รับเข้า
                            </a>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($alertInks->take(4) as $alert)
                            <span class="badge bg-white text-dark border shadow-none small py-1 px-2">
                                <span class="badge-color-dot" style="width: 8px; height: 8px; background-color: {{ $alert->color_code ?? '#333' }};"></span>
                                {{ $alert->model }}: <strong class="{{ $alert->current_stock <= 0 ? 'text-danger' : 'text-warning' }}">{{ $alert->current_stock }}</strong>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Requisitions Table -->
            <div class="card bg-white border-0 shadow-sm flex-grow-1">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i>
                        ประวัติการเบิกล่าสุด
                    </h6>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.78rem;">
                        ดูทั้งหมด
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>วันที่</th>
                                <th>รุ่นหมึก</th>
                                <th class="text-center">จำนวน</th>
                                <th>ผู้ขอเบิก</th>
                                <th class="text-center">สลิป</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions->where('type', 'out')->take(6) as $tx)
                                <tr>
                                    <td>
                                        <small class="text-dark fw-medium">{{ $tx->action_date->format('d/m') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge-color-dot flex-shrink-0" style="width: 8px; height: 8px; background-color: {{ $tx->ink->color_code ?? '#333' }};"></span>
                                            <span class="fw-semibold text-dark small">{{ $tx->ink->model ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning-subtle text-dark border small py-1 px-2 fw-bold">
                                            {{ $tx->quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-dark d-block fw-medium text-truncate" style="max-width: 110px;">{{ $tx->person_name }}</small>
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $tx->department_name ?: '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('transactions.show', $tx) }}" class="btn btn-sm btn-light border py-0 px-2 text-primary" title="ดูสลิป">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        ยังไม่มีประวัติการเบิกหมึก
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('quickInkSearch');
        const rows = document.querySelectorAll('.ink-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();

                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search') || '';
                    if (query === '' || searchData.includes(query)) {
                        row.classList.remove('d-none');
                    } else {
                        row.classList.add('d-none');
                    }
                });
            });
        }
    });
</script>
@endpush
