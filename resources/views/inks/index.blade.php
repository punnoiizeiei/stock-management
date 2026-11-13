@extends('layouts.app')

@section('title', 'จัดการคลังหมึกพิมพ์')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-box-seam text-primary"></i>
            คลังหมึกพิมพ์ (Ink Inventory)
        </h4>
        <p class="text-muted small mb-0">ตรวจสอบจำนวนคงเหลือ จุดเตือนหมึกใกล้หมด และค้นหาหมึกที่รองรับกับเครื่องพิมพ์ของคุณ</p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('inks.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-lg"></i> เพิ่มรุ่นหมึกใหม่
            </a>
        @endif
    </div>
</div>

<!-- Filter & Search Bar -->
<div class="card bg-white p-3 mb-4">
    <form action="{{ route('inks.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="ค้นหา รหัสหมึก, ยี่ห้อ, รุ่น, เครื่องพิมพ์ที่รองรับ...">
            </div>
        </div>
        <div class="col-md-3">
            <select name="brand" class="form-select">
                <option value="">-- ยี่ห้อทั้งหมด --</option>
                @foreach($brands as $b)
                    <option value="{{ $b }}" {{ request('brand') == $b ? 'selected' : '' }}>{{ $b }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">-- สถานะสต็อกทั้งหมด --</option>
                <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>ปกติ</option>
                <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>ใกล้หมด</option>
                <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>หมดสต็อก</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-dark w-100">
                <i class="bi bi-funnel"></i> กรอง
            </button>
            @if(request('search') || request('brand') || request('status'))
                <a href="{{ route('inks.index') }}" class="btn btn-light border" title="ล้างค่า">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Inks Table -->
<div class="card bg-white shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 120px;">รหัสหมึก</th>
                    <th>ยี่ห้อ / รุ่น</th>
                    <th>สี & ประเภท</th>
                    <th>เครื่องพิมพ์ที่รองรับ</th>
                    <th>สถานที่จัดเก็บ</th>
                    <th class="text-center" style="width: 120px;">คงเหลือ</th>
                    <th class="text-center" style="width: 100px;">สถานะ</th>
                    <th class="text-end" style="width: {{ (auth()->check() && auth()->user()->isAdmin()) ? '180px' : '120px' }};">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inks as $ink)
                    <tr>
                        <td>
                            <a href="{{ route('inks.show', $ink) }}" class="fw-bold text-decoration-none text-primary">
                                {{ $ink->code }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $ink->model }}</div>
                            <small class="text-muted d-block">{{ $ink->name }}</small>
                            <span class="badge bg-light text-dark border mt-1" style="font-size: 0.72rem;">{{ $ink->brand }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <span class="badge-color-dot" style="background-color: {{ $ink->color_code }}"></span>
                                <span class="small fw-medium">{{ $ink->color }}</span>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $ink->type }}</small>
                        </td>
                        <td>
                            <span class="small text-muted text-break" style="max-width: 260px; display: inline-block;">
                                {{ $ink->compatible_printers ?: '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border">
                                <i class="bi bi-geo-alt me-1"></i>{{ $ink->location ?: 'ไม่ได้ระบุ' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="fs-5 fw-bold {{ $ink->current_stock <= 0 ? 'text-danger' : ($ink->current_stock <= $ink->min_stock ? 'text-warning' : 'text-success') }}">
                                {{ $ink->current_stock }}
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $ink->unit }} (เตือน: {{ $ink->min_stock }})</small>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $ink->status_badge_class }} py-1 px-2">
                                {{ $ink->status_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('transactions.stock_out', ['ink_id' => $ink->id]) }}" 
                                   class="btn btn-outline-warning text-dark {{ $ink->current_stock <= 0 ? 'disabled' : '' }}" 
                                   title="เบิกหมึก">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                @if(auth()->check() && auth()->user()->isAdmin())
                                    <a href="{{ route('transactions.stock_in', ['ink_id' => $ink->id]) }}" 
                                       class="btn btn-outline-success" 
                                       title="รับเข้าสต็อก">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                @endif
                                <a href="{{ route('inks.show', $ink) }}" class="btn btn-outline-primary" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->check() && auth()->user()->isAdmin())
                                    <a href="{{ route('inks.edit', $ink) }}" class="btn btn-outline-secondary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบข้อมูลหมึกตามเงื่อนไขที่ค้นหา
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($inks->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">แสดง {{ $inks->firstItem() }} ถึง {{ $inks->lastItem() }} จากทั้งหมด {{ $inks->total() }} รายการ</small>
            <div>{{ $inks->links('pagination::bootstrap-5') }}</div>
        </div>
    @endif
</div>
@endsection
