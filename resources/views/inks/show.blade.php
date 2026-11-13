@extends('layouts.app')

@section('title', $ink->name . ' - รายละเอียดหมึกพิมพ์')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge-color-dot" style="background-color: {{ $ink->color_code }}; width: 16px; height: 16px;"></span>
            <h4 class="fw-bold mb-0">{{ $ink->name }}</h4>
            <span class="badge {{ $ink->status_badge_class }}">{{ $ink->status_label }}</span>
        </div>
        <p class="text-muted small mb-0">รหัสหมึก: <strong class="text-dark">{{ $ink->code }}</strong> &bull; ยี่ห้อ: {{ $ink->brand }} &bull; รุ่น: {{ $ink->model }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('transactions.stock_out', ['ink_id' => $ink->id]) }}" class="btn btn-warning text-dark fw-semibold {{ $ink->current_stock <= 0 ? 'disabled' : '' }}">
            <i class="bi bi-box-arrow-up-right me-1"></i> เบิกหมึกรุ่นนี้
        </a>

        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('transactions.stock_in', ['ink_id' => $ink->id]) }}" class="btn btn-success fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> รับเข้าสต็อก
            </a>
            <a href="{{ route('inks.edit', $ink) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> แก้ไข
            </a>
            <form action="{{ route('inks.destroy', $ink) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการหมึกนี้? ประวัติการเบิก-รับที่เกี่ยวข้องจะถูกลบด้วย');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left: Stock Status Card -->
    <div class="col-lg-4">
        <div class="card bg-white p-4 h-100 shadow-sm">
            <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                สถานะคงคลังปัจจุบัน
            </h6>

            <div class="text-center py-3 bg-light rounded-3 mb-4 border">
                <div class="display-4 fw-bold {{ $ink->current_stock <= 0 ? 'text-danger' : ($ink->current_stock <= $ink->min_stock ? 'text-warning' : 'text-success') }}">
                    {{ $ink->current_stock }}
                </div>
                <div class="fw-semibold text-secondary">{{ $ink->unit }} คงเหลือ</div>
                <div class="small text-muted mt-1">จุดเตือนขั้นต่ำ: {{ $ink->min_stock }} {{ $ink->unit }}</div>
            </div>

            <div class="list-group list-group-flush small">
                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                    <span class="text-muted">สีหมึก:</span>
                    <span class="fw-medium d-flex align-items-center gap-1">
                        <span class="badge-color-dot" style="background-color: {{ $ink->color_code }}"></span>
                        {{ $ink->color }}
                    </span>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                    <span class="text-muted">ประเภทหมึก:</span>
                    <span class="fw-medium">{{ $ink->type }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0 py-2">
                    <span class="text-muted">สถานที่จัดเก็บ:</span>
                    <span class="fw-medium text-primary">
                        <i class="bi bi-geo-alt"></i> {{ $ink->location ?: 'ไม่ได้ระบุ' }}
                    </span>
                </div>
                @if(auth()->check() && auth()->user()->isAdmin())
                    <div class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">ราคาต่อหน่วย:</span>
                        <span class="fw-medium">{{ $ink->price_per_unit > 0 ? number_format($ink->price_per_unit, 2) . ' บาท' : 'ไม่ได้ระบุ' }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right: Compatible Printers & Notes -->
    <div class="col-lg-8">
        <div class="card bg-white p-4 h-100 shadow-sm">
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-printer text-primary"></i>
                เครื่องพิมพ์ที่รองรับการใช้งาน
            </h6>
            <div class="p-3 bg-light rounded-3 border mb-4">
                @if($ink->compatible_printers)
                    <div class="fw-medium text-dark">{{ $ink->compatible_printers }}</div>
                @else
                    <span class="text-muted">ไม่ได้ระบุรุ่นเครื่องพิมพ์ที่รองรับ</span>
                @endif
            </div>

            <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-info-circle text-secondary"></i>
                หมายเหตุ / ข้อมูลเพิ่มเติม
            </h6>
            <div class="p-3 bg-light rounded-3 border mb-3">
                {{ $ink->notes ?: 'ไม่มีหมายเหตุเพิ่มเติม' }}
            </div>

            <div class="row g-2 mt-auto pt-2">
                <div class="col-sm-6">
                    <div class="border rounded-3 p-3 text-center">
                        <div class="text-muted small">ยอดเบิกรวมทั้งหมด</div>
                        <div class="fs-5 fw-bold text-primary mt-1">
                            {{ number_format($ink->transactions()->where('type', 'out')->sum('quantity')) }} {{ $ink->unit }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="border rounded-3 p-3 text-center">
                        <div class="text-muted small">ยอดรับเข้ารวมทั้งหมด</div>
                        <div class="fs-5 fw-bold text-success mt-1">
                            {{ number_format($ink->transactions()->where('type', 'in')->sum('quantity')) }} {{ $ink->unit }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction History for this ink -->
<div class="card bg-white shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary"></i>
            ประวัติการเบิก-รับ ของหมึกรุ่นนี้
        </h6>
        <span class="badge bg-light text-dark border">{{ $transactions->total() }} รายการ</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>วันที่ / เลขที่</th>
                    <th>ประเภท</th>
                    <th class="text-center">จำนวน</th>
                    <th class="text-center">สต็อกก่อน-หลัง</th>
                    <th>ผู้ขอเบิก / ผู้รับ</th>
                    <th>แผนก / ปลายทาง</th>
                    <th>หมายเหตุ / เลขที่อ้างอิง</th>
                    <th class="text-center">สลิป</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
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
                        <td class="text-center">
                            <span class="fw-bold {{ $tx->type === 'in' ? 'text-success' : 'text-primary' }}">
                                {{ $tx->type === 'in' ? '+' : '-' }}{{ $tx->quantity }}
                            </span>
                            <small class="text-muted">{{ $ink->unit }}</small>
                        </td>
                        <td class="text-center small">
                            <span class="text-muted">{{ $tx->stock_before }}</span>
                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                            <strong>{{ $tx->stock_after }}</strong>
                        </td>
                        <td>
                            <div class="small fw-medium">{{ $tx->person_name }}</div>
                        </td>
                        <td>
                            <div class="small">{{ $tx->department_name ?: '-' }}</div>
                            @if($tx->printer_target)
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    <i class="bi bi-printer"></i> {{ $tx->printer_target }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <span class="small text-muted">{{ $tx->notes ?: '-' }}</span>
                            @if($tx->reference_no)
                                <small class="d-block text-secondary" style="font-size: 0.72rem;">อ้างอิง: {{ $tx->reference_no }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('transactions.show', $tx) }}" class="btn btn-sm btn-light border py-1 px-2" title="พิมพ์สลิป">
                                <i class="bi bi-receipt"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">ยังไม่มีประวัติการเบิกหรือรับเข้าของหมึกรุ่นนี้</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
