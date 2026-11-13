@extends('layouts.app')

@section('title', 'ประวัติการเบิก-รับหมึกพิมพ์')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary"></i>
            ประวัติการเบิก-รับหมึกพิมพ์ (Transaction History)
        </h4>
        <p class="text-muted small mb-0">ตรวจสอบและค้นหารายการเบิกจ่ายและรับเข้าสต็อกย้อนหลัง พร้อมพิมพ์ใบเบิก/ใบเสร็จ</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if(auth()->check() && auth()->user()->isAdmin())
            <button type="button" class="btn btn-outline-danger btn-sm fw-medium d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#resetHistoryModal">
                <i class="bi bi-trash3"></i> ล้างประวัติ / เริ่มนับ 1 ใหม่
            </button>
        @endif
        <a href="{{ route('transactions.stock_out') }}" class="btn btn-warning text-dark fw-semibold shadow-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> เบิกหมึก
        </a>
        <a href="{{ route('transactions.stock_in') }}" class="btn btn-success fw-semibold shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> รับหมึกเข้า
        </a>
    </div>
</div>

<!-- Search & Filters -->
<div class="card bg-white p-3 mb-4">
    <form action="{{ route('transactions.index') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted">ค้นหาทั่วไป</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="เลขที่, ผู้ทำรายการ, PO, เครื่องพิมพ์...">
            </div>
        </div>

        <div class="col-md-2">
            <label class="form-label small fw-semibold text-muted">ประเภทรายการ</label>
            <select name="type" class="form-select">
                <option value="">-- ทั้งหมด --</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>📤 เบิกใช้งาน (Stock Out)</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>📥 รับเข้าสต็อก (Stock In)</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small fw-semibold text-muted">รุ่นหมึก</label>
            <select name="ink_id" class="form-select">
                <option value="">-- ทุกรุ่น --</option>
                @foreach($inks as $ink)
                    <option value="{{ $ink->id }}" {{ request('ink_id') == $ink->id ? 'selected' : '' }}>
                        {{ $ink->brand }} {{ $ink->model }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small fw-semibold text-muted">แผนก / ฝ่าย</label>
            <select name="department_id" class="form-select">
                <option value="">-- ทุกแผนก --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <div class="flex-grow-1">
                <label class="form-label small fw-semibold text-muted">ตั้งแต่วันที่</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="flex-grow-1">
                <label class="form-label small fw-semibold text-muted">ถึงวันที่</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div>
                <label class="form-label small fw-semibold text-transparent d-block">ค้นหา</label>
                <button type="submit" class="btn btn-dark" title="กรองข้อมูล">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
            @if(request()->hasAny(['search', 'type', 'ink_id', 'department_id', 'date_from', 'date_to']))
                <div>
                    <label class="form-label small fw-semibold text-transparent d-block">ล้าง</label>
                    <a href="{{ route('transactions.index') }}" class="btn btn-light border" title="ล้างตัวกรอง">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            @endif
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="card bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px;">วันที่ / เลขที่รายการ</th>
                    <th style="width: 110px;">ประเภท</th>
                    <th>รายการหมึกพิมพ์</th>
                    <th class="text-center" style="width: 100px;">จำนวน</th>
                    <th class="text-center" style="width: 130px;">สต็อก (ก่อน &rarr; หลัง)</th>
                    <th>ผู้ทำรายการ</th>
                    <th>แผนก / ปลายทาง</th>
                    <th>เลขอ้างอิง / หมายเหตุ</th>
                    <th class="text-center" style="width: 90px;">เอกสาร</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $tx->action_date->format('d/m/Y') }}</div>
                            <span class="text-muted small" style="font-size: 0.78rem;">{{ $tx->transaction_code }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $tx->type_badge_class }} py-1 px-2">
                                {{ $tx->type_label }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge-color-dot" style="background-color: {{ $tx->ink->color_code ?? '#222' }}"></span>
                                <div>
                                    <a href="{{ route('inks.show', $tx->ink) }}" class="fw-semibold text-decoration-none text-dark">
                                        {{ $tx->ink->model ?? 'ไม่ระบุ' }}
                                    </a>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                                        {{ $tx->ink->brand ?? '' }} &bull; {{ $tx->ink->name ?? '' }}
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="fs-6 fw-bold {{ $tx->type === 'in' ? 'text-success' : 'text-primary' }}">
                                {{ $tx->type === 'in' ? '+' : '-' }}{{ $tx->quantity }}
                            </span>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $tx->ink->unit ?? 'หน่วย' }}</small>
                        </td>
                        <td class="text-center small">
                            <span class="text-muted">{{ $tx->stock_before }}</span>
                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                            <strong class="{{ $tx->stock_after <= 0 ? 'text-danger' : 'text-dark' }}">{{ $tx->stock_after }}</strong>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $tx->person_name }}</div>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $tx->type === 'in' ? 'ผู้รับพัสดุ' : 'ผู้ขอเบิก' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $tx->department_name ?: 'ไม่ระบุ' }}</span>
                            @if($tx->printer_target)
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-printer"></i> {{ $tx->printer_target }}
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($tx->reference_no)
                                <span class="badge bg-secondary-subtle text-secondary border d-inline-block mb-1" style="font-size: 0.72rem;">
                                    {{ $tx->reference_no }}
                                </span>
                            @endif
                            <div class="small text-muted text-truncate" style="max-width: 180px;">
                                {{ $tx->notes ?: '-' }}
                            </div>
                        </td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('transactions.show', $tx) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="ดู / พิมพ์ใบสำคัญ">
                                <i class="bi bi-receipt"></i> สลิป
                            </a>
                            @if(auth()->check() && auth()->user()->isAdmin())
                                <form action="{{ route('transactions.destroy', $tx) }}" method="POST" class="d-inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการ {{ $tx->transaction_code }} นี้?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="ลบรายการนี้">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบรายการประวัติการเบิก-รับ ตามเงื่อนไขที่ค้นหา
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">แสดง {{ $transactions->firstItem() }} ถึง {{ $transactions->lastItem() }} จากทั้งหมด {{ $transactions->total() }} รายการ</small>
            <div>{{ $transactions->links('pagination::bootstrap-5') }}</div>
        </div>
    @endif
</div>

<!-- Modal: Reset History -->
@if(auth()->check() && auth()->user()->isAdmin())
<div class="modal fade" id="resetHistoryModal" tabindex="-1" aria-labelledby="resetHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('transactions.reset_history') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger-subtle text-danger border-0 py-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="resetHistoryModalLabel">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        ยืนยันการล้างประวัติ & รันเลข 1 ใหม่
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger py-2 px-3 small mb-3">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        <strong>คำเตือน:</strong> ข้อมูลประวัติการเบิกและการรับหมึกทั้งหมดจะถูกลบออกจากฐานข้อมูลอย่างถาวร
                    </div>

                    <p class="text-dark small mb-3">
                        เมื่อยืนยันแล้ว ระบบจะ:
                    </p>
                    <ul class="text-muted small ps-3 mb-3">
                        <li>ลบรายการประวัติการเบิก-รับหมึกทั้งหมด</li>
                        <li><strong>รีเซ็ตการนับเลขที่เอกสารกลับไปเริ่มต้นที่ 1</strong> (เช่น <code>REQ-{{ date('Ymd') }}-0001</code> หรือ <code>RC-{{ date('Ymd') }}-0001</code>)</li>
                        <li>รหัสลำดับในฐานข้อมูล (ID) จะเริ่มนับจาก 1 ใหม่อัตโนมัติ</li>
                    </ul>

                    <div class="form-check bg-light p-3 rounded-2 border">
                        <input class="form-check-input" type="checkbox" name="reset_ink_stock" value="1" id="resetInkStockCheck">
                        <label class="form-check-label small fw-medium text-dark" for="resetInkStockCheck">
                            รีเซ็ตยอดสต็อกคงเหลือของหมึกทุกรุ่นในระบบให้เป็น 0 ด้วย (ถ้าไม่ติ๊ก ยอดสต็อกจะคงเดิม)
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">
                        <i class="bi bi-trash3 me-1"></i> ยืนยันล้างประวัติและเริ่มนับ 1 ใหม่
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

