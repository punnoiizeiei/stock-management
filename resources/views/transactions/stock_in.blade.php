@extends('layouts.app')

@section('title', 'บันทึกการรับหมึกเข้าสต็อก')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <span class="p-2 bg-success-subtle text-success rounded-3 d-inline-flex">
                        <i class="bi bi-box-arrow-in-down fs-5"></i>
                    </span>
                    บันทึกการรับหมึกเข้าสต็อก (Stock In)
                </h4>
                <p class="text-muted small mb-0">บันทึกการรับหมึกเข้าคลังสินค้า ปรับเพิ่มยอดคงเหลืออัตโนมัติ</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ดูประวัติทั้งหมด
            </a>
        </div>

        <div class="card bg-white p-4 shadow-sm">
            <form action="{{ route('transactions.stock_in.store') }}" method="POST">
                @csrf

                <!-- Step 1: Select Ink -->
                <div class="mb-4 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-dark fs-6 mb-0">
                            1. เลือกรุ่นหมึกที่ต้องการรับเข้า <span class="text-danger">*</span>
                        </label>
                        <a href="{{ route('inks.create') }}" class="small text-decoration-none text-primary fw-medium" target="_blank">
                            <i class="bi bi-plus"></i> เพิ่มรุ่นหมึกใหม่หากยังไม่มีในระบบ
                        </a>
                    </div>
                    <select name="ink_id" id="inkSelect" class="form-select form-select-lg @error('ink_id') is-invalid @enderror" required>
                        <option value="">-- กรุณาเลือกรุ่นหมึกพิมพ์ --</option>
                        @foreach($inks as $ink)
                            <option value="{{ $ink->id }}" 
                                    data-stock="{{ $ink->current_stock }}"
                                    data-unit="{{ $ink->unit }}"
                                    data-brand="{{ $ink->brand }}"
                                    data-color="{{ $ink->color }}"
                                    data-color-code="{{ $ink->color_code }}"
                                    data-location="{{ $ink->location }}"
                                    data-printers="{{ $ink->compatible_printers }}"
                                    {{ old('ink_id', $selectedInkId) == $ink->id ? 'selected' : '' }}>
                                [{{ $ink->code }}] {{ $ink->brand }} {{ $ink->model }} ({{ $ink->color }}) - ปัจจุบันมี: {{ $ink->current_stock }} {{ $ink->unit }}
                            </option>
                        @endforeach
                    </select>
                    @error('ink_id') <div class="invalid-feedback">{{ $message }}</div> @enderror

                    <!-- Dynamic Ink Info Box -->
                    <div id="inkInfoBox" class="mt-3 p-3 bg-light rounded-3 border d-none">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span id="previewColorDot" class="badge-color-dot" style="width: 14px; height: 14px;"></span>
                                    <strong id="previewModel" class="fs-6 text-dark"></strong>
                                    <span id="previewBrand" class="badge bg-secondary"></span>
                                </div>
                                <div class="small text-muted" id="previewPrinters"></div>
                                <div class="small text-secondary mt-1">
                                    <i class="bi bi-geo-alt"></i> นำไปจัดเก็บที่: <span id="previewLocation" class="fw-medium text-dark"></span>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-end border-start-md">
                                <div class="text-muted small">ยอดคงเหลือก่อนรับเข้า</div>
                                <div class="fs-4 fw-bold text-dark" id="previewStockText"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Receiving Details -->
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark fs-6 mb-0">
                            2. ข้อมูลการรับเข้า
                        </label>
                    </div>

                    <!-- Quantity -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">จำนวนที่รับเข้า <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="quantity" id="quantityInput" value="{{ old('quantity', 1) }}" min="1" class="form-control form-control-lg @error('quantity') is-invalid @enderror" required>
                            <span class="input-group-text bg-light fw-medium" id="unitLabel">ตลับ/ขวด</span>
                        </div>
                        <div class="form-text text-success" id="newStockPreview">
                            <!-- JS calculated new total -->
                        </div>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Department / Source -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">แผนกผู้รับผิดชอบ / จัดซื้อ</label>
                        <select name="department_id" class="form-select form-select-lg">
                            <option value="">-- ไม่ระบุ / จัดซื้อทั่วไป --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', auth()->user()?->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Receiver Name -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ชื่อผู้รับเข้า / ตรวจรับ <span class="text-danger">*</span></label>
                        <input type="text" name="person_name" value="{{ old('person_name', auth()->user()?->name) }}" class="form-control form-control-lg @error('person_name') is-invalid @enderror" placeholder="ชื่อ-นามสกุล ผู้ตรวจรับ" required>
                        @error('person_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- PO / Invoice Reference -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">เลขที่ใบสั่งซื้อ (PO) / ใบส่งของ / ใบกำกับภาษี</label>
                        <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control" placeholder="เช่น PO-69108, INV-2026/045">
                    </div>

                    <!-- Action Date -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">วันที่รับเข้าพัสดุ <span class="text-danger">*</span></label>
                        <input type="date" name="action_date" value="{{ old('action_date', date('Y-m-d')) }}" class="form-control @error('action_date') is-invalid @enderror" required>
                        @error('action_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">หมายเหตุ / ข้อมูลผู้จำหน่าย (Supplier)</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="เช่น ซื้อจาก บจก. สยามออฟฟิศ, สินค้าล็อตใหม่วันหมดอายุ 2028">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted small">ระบบจะเพิ่มยอดเข้าสู่คลังสินค้าทันทีหลังจากบันทึก</span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-light border px-4">ยกเลิก</a>
                        <button type="submit" class="btn btn-success px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> ยืนยันการรับเข้าสต็อก
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inkSelect = document.getElementById('inkSelect');
        const inkInfoBox = document.getElementById('inkInfoBox');
        const previewColorDot = document.getElementById('previewColorDot');
        const previewModel = document.getElementById('previewModel');
        const previewBrand = document.getElementById('previewBrand');
        const previewPrinters = document.getElementById('previewPrinters');
        const previewLocation = document.getElementById('previewLocation');
        const previewStockText = document.getElementById('previewStockText');
        const unitLabel = document.getElementById('unitLabel');
        const quantityInput = document.getElementById('quantityInput');
        const newStockPreview = document.getElementById('newStockPreview');

        let currentStockAvailable = 0;
        let currentUnit = 'ตลับ';

        function updateInkPreview() {
            const selected = inkSelect.options[inkSelect.selectedIndex];
            if (!selected || !selected.value) {
                inkInfoBox.classList.add('d-none');
                newStockPreview.textContent = '';
                return;
            }

            const stock = parseInt(selected.getAttribute('data-stock') || 0);
            const unit = selected.getAttribute('data-unit') || 'ตลับ';
            const brand = selected.getAttribute('data-brand') || '';
            const colorCode = selected.getAttribute('data-color-code') || '#111';
            const location = selected.getAttribute('data-location') || 'ไม่ได้ระบุ';
            const printers = selected.getAttribute('data-printers') || 'ไม่ระบุรุ่นที่รองรับ';

            currentStockAvailable = stock;
            currentUnit = unit;

            previewColorDot.style.backgroundColor = colorCode;
            previewModel.textContent = selected.text.split(' - ปัจจุบันมี:')[0];
            previewBrand.textContent = brand;
            previewPrinters.innerHTML = '<i class="bi bi-printer me-1"></i> รองรับ: ' + printers;
            previewLocation.textContent = location;
            previewStockText.innerHTML = `${stock} <span class="fs-6 text-muted">${unit}</span>`;
            unitLabel.textContent = unit;

            inkInfoBox.classList.remove('d-none');
            calcNewTotal();
        }

        function calcNewTotal() {
            const added = parseInt(quantityInput.value || 0);
            const total = currentStockAvailable + added;
            newStockPreview.innerHTML = `<i class="bi bi-arrow-up-circle"></i> หลังรับเข้า ยอดจะเพิ่มเป็น <strong>${total} ${currentUnit}</strong>`;
        }

        inkSelect.addEventListener('change', updateInkPreview);
        quantityInput.addEventListener('input', calcNewTotal);

        if (inkSelect.value) {
            updateInkPreview();
        }
    });
</script>
@endpush

