@extends('layouts.app')

@section('title', 'บันทึกการเบิกหมึกพิมพ์')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <span class="p-2 bg-warning-subtle text-warning rounded-3 d-inline-flex">
                        <i class="bi bi-box-arrow-up-right fs-5 text-dark"></i>
                    </span>
                    บันทึกการเบิกหมึกพิมพ์ (Stock Out)
                </h4>
                <p class="text-muted small mb-0">พนักงานทุกท่านสามารถเลือกหมึกและกรอกข้อมูลเพื่อเบิกได้ทันที โดยไม่ต้องเข้าสู่ระบบ</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ดูประวัติทั้งหมด
            </a>
        </div>

        <div class="card bg-white p-4 shadow-sm">
            <form action="{{ route('transactions.stock_out.store') }}" method="POST" id="stockOutForm">
                @csrf

                <!-- Step 1: Select Ink -->
                <div class="mb-4 pb-3 border-bottom">
                    <label class="form-label fw-bold text-dark fs-6">
                        1. เลือกรุ่นหมึกที่ต้องการเบิก <span class="text-danger">*</span>
                    </label>
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
                                    {{ old('ink_id', $selectedInkId) == $ink->id ? 'selected' : '' }}
                                    {{ $ink->current_stock <= 0 ? 'disabled' : '' }}>
                                [{{ $ink->code }}] {{ $ink->brand }} {{ $ink->model }} ({{ $ink->color }}) - คงเหลือ: {{ $ink->current_stock }} {{ $ink->unit }}
                                {{ $ink->current_stock <= 0 ? '(หมดสต็อก)' : '' }}
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
                                    <i class="bi bi-geo-alt"></i> สถานที่เก็บ: <span id="previewLocation" class="fw-medium text-dark"></span>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-end border-start-md">
                                <div class="text-muted small">คงเหลือพร้อมเบิก</div>
                                <div class="fs-4 fw-bold" id="previewStockText"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Quantity & Requester Details -->
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark fs-6 mb-0">
                            2. ข้อมูลการเบิกจ่าย
                        </label>
                    </div>

                    <!-- Quantity -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">จำนวนที่ขอเบิก <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="quantity" id="quantityInput" value="{{ old('quantity', 1) }}" min="1" class="form-control form-control-lg @error('quantity') is-invalid @enderror" required>
                            <span class="input-group-text bg-light fw-medium" id="unitLabel">ตลับ/ขวด</span>
                        </div>
                        <div class="form-text text-danger d-none" id="stockExceededWarning">
                            <i class="bi bi-exclamation-circle"></i> จำนวนที่ขอเบิกมากกว่าสต็อกคงเหลือ!
                        </div>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Department -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">แผนก / ฝ่าย ที่ขอเบิก <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select form-select-lg @error('department_id') is-invalid @enderror" required>
                            <option value="">-- เลือกแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', auth()->user()?->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Requester Person Name -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ชื่อผู้ขอเบิก <span class="text-danger">*</span></label>
                        <input type="text" name="person_name" value="{{ old('person_name', auth()->user()?->name) }}" class="form-control form-control-lg @error('person_name') is-invalid @enderror" placeholder="ชื่อ-นามสกุล ผู้ขอเบิก" required>
                        @error('person_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Printer target / Room -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">สำหรับเครื่องพิมพ์ / จุดติดตั้ง</label>
                        <input type="text" name="printer_target" value="{{ old('printer_target') }}" class="form-control" placeholder="เช่น HP LaserJet ชั้น 3 ห้องบัญชี, โต๊ะหน้าห้องประชาสัมพันธ์">
                    </div>

                    <!-- Reference Number -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">เลขที่เอกสาร / ใบขอเบิก</label>
                        <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control" placeholder="เช่น REQ-105, บันทึกข้อความ">
                    </div>

                    <!-- Action Date -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">วันที่เบิก <span class="text-danger">*</span></label>
                        <input type="date" name="action_date" value="{{ old('action_date', date('Y-m-d')) }}" class="form-control @error('action_date') is-invalid @enderror" required>
                        @error('action_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">วัตถุประสงค์ / หมายเหตุเพิ่มเติม</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="ระบุเหตุผลในการเบิก เช่น ตลับเดิมหมึกจาง, เตรียมสำหรับงานสัมมนาประจำปี">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted small">ระบบจะทำการตัดยอดสต็อกคงเหลือทันทีหลังจากกดบันทึก</span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-light border px-4">ยกเลิก</a>
                        <button type="submit" id="submitBtn" class="btn btn-warning text-dark px-4 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> ยืนยันการเบิกหมึก
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
        const stockExceededWarning = document.getElementById('stockExceededWarning');
        const submitBtn = document.getElementById('submitBtn');

        let currentStockAvailable = 0;

        function updateInkPreview() {
            const selected = inkSelect.options[inkSelect.selectedIndex];
            if (!selected || !selected.value) {
                inkInfoBox.classList.add('d-none');
                currentStockAvailable = 0;
                return;
            }

            const stock = parseInt(selected.getAttribute('data-stock') || 0);
            const unit = selected.getAttribute('data-unit') || 'ตลับ';
            const brand = selected.getAttribute('data-brand') || '';
            const color = selected.getAttribute('data-color') || '';
            const colorCode = selected.getAttribute('data-color-code') || '#111';
            const location = selected.getAttribute('data-location') || 'ไม่ได้ระบุ';
            const printers = selected.getAttribute('data-printers') || 'ไม่ระบุรุ่นที่รองรับ';

            currentStockAvailable = stock;

            previewColorDot.style.backgroundColor = colorCode;
            previewModel.textContent = selected.text.split(' - คงเหลือ:')[0];
            previewBrand.textContent = brand;
            previewPrinters.innerHTML = '<i class="bi bi-printer me-1"></i> รองรับ: ' + printers;
            previewLocation.textContent = location;
            previewStockText.innerHTML = (stock > 0 ? `<span class="text-success">${stock}</span>` : `<span class="text-danger">0</span>`) + ` <span class="fs-6 text-muted">${unit}</span>`;
            unitLabel.textContent = unit;

            inkInfoBox.classList.remove('d-none');
            validateQuantity();
        }

        function validateQuantity() {
            const requested = parseInt(quantityInput.value || 0);
            if (currentStockAvailable > 0 && requested > currentStockAvailable) {
                stockExceededWarning.classList.remove('d-none');
                submitBtn.disabled = true;
            } else if (currentStockAvailable <= 0) {
                submitBtn.disabled = true;
            } else {
                stockExceededWarning.classList.add('d-none');
                submitBtn.disabled = false;
            }
        }

        inkSelect.addEventListener('change', updateInkPreview);
        quantityInput.addEventListener('input', validateQuantity);

        // Run initially if ink is preselected
        if (inkSelect.value) {
            updateInkPreview();
        }
    });
</script>
@endpush

