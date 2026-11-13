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
            <form action="{{ route('transactions.stock_in.store') }}" method="POST" id="stockInForm">
                @csrf

                <!-- Step 1: Select Ink with Autocomplete -->
                <div class="mb-4 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-dark fs-6 mb-0">
                            1. เลือกรุ่นหมึกที่ต้องการรับเข้า <span class="text-danger">*</span>
                        </label>
                        <a href="{{ route('inks.create') }}" class="small text-decoration-none text-primary fw-medium" target="_blank">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มรุ่นหมึกใหม่หากยังไม่มีในระบบ
                        </a>
                    </div>

                    <!-- Autocomplete Input Box -->
                    <div class="position-relative">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   id="inkSearchInput" 
                                   class="form-control border-start-0 @error('ink_id') is-invalid @enderror" 
                                   placeholder="🔍 คลิกหรือพิมพ์ค้นหารหัสหมึก, ยี่ห้อ, รุ่น, หรือเครื่องพิมพ์ เช่น 003, Canon, Brother, L3110..." 
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary d-none" type="button" id="clearInkBtn" title="ล้างการเลือกเพื่อเลือกใหม่">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        
                        <!-- Autocomplete Suggestions List -->
                        <div id="inkSuggestions" 
                             class="position-absolute w-100 bg-white border rounded-3 shadow-lg p-0 mt-1 d-none" 
                             style="top: 100%; left: 0; max-height: 380px; overflow-y: auto; z-index: 1050; display: none;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                    @error('ink_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                    <!-- Hidden Native Select to retain full form compatibility -->
                    <select name="ink_id" id="inkSelect" class="d-none" required>
                        <option value="">-- กรุณาเลือกรุ่นหมึกพิมพ์ --</option>
                        @foreach($inks as $ink)
                            <option value="{{ $ink->id }}" 
                                    data-code="{{ $ink->code }}"
                                    data-stock="{{ $ink->current_stock }}"
                                    data-unit="{{ $ink->unit }}"
                                    data-brand="{{ $ink->brand }}"
                                    data-model="{{ $ink->model }}"
                                    data-color="{{ $ink->color }}"
                                    data-color-code="{{ $ink->color_code }}"
                                    data-location="{{ $ink->location }}"
                                    data-printers="{{ $ink->compatible_printers }}"
                                    {{ old('ink_id', $selectedInkId) == $ink->id ? 'selected' : '' }}>
                                [{{ $ink->code }}] {{ $ink->brand }} {{ $ink->model }} ({{ $ink->color }}) - ปัจจุบันมี: {{ $ink->current_stock }} {{ $ink->unit }}
                            </option>
                        @endforeach
                    </select>

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
                        <select name="department_id" id="departmentSelect" class="form-select form-select-lg">
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
                        <input type="text" name="person_name" id="personNameInput" value="{{ old('person_name', auth()->user()?->name) }}" class="form-control form-control-lg @error('person_name') is-invalid @enderror" placeholder="ชื่อ-นามสกุล ผู้ตรวจรับ" required>
                        @error('person_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- PO / Invoice Reference -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">เลขที่ใบสั่งซื้อ (PO) / ใบส่งของ / ใบกำกับภาษี</label>
                        <input type="text" name="reference_no" id="referenceNoInput" value="{{ old('reference_no') }}" class="form-control" placeholder="เช่น PO-69108, INV-2026/045">
                    </div>

                    <!-- Action Date -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">วันที่รับเข้าพัสดุ <span class="text-danger">*</span></label>
                        <input type="date" name="action_date" id="actionDateInput" value="{{ old('action_date', date('Y-m-d')) }}" class="form-control @error('action_date') is-invalid @enderror" required>
                        @error('action_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">หมายเหตุ / ข้อมูลผู้จำหน่าย (Supplier)</label>
                        <textarea name="notes" id="notesInput" rows="2" class="form-control" placeholder="เช่น ซื้อจาก บจก. สยามออฟฟิศ, สินค้าล็อตใหม่วันหมดอายุ 2028">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted small">ระบบจะเพิ่มยอดเข้าสู่คลังสินค้าทันทีหลังจากบันทึก</span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-light border px-4">ยกเลิก</a>
                        <button type="button" id="openConfirmModalBtn" class="btn btn-success px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> ยืนยันการรับเข้าสต็อก
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Popup (Modal) -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success-subtle text-dark border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="confirmModalLabel">
                    <i class="bi bi-box-arrow-in-down text-success fs-4"></i>
                    ยืนยันการทำรายการรับหมึกเข้าสต็อก
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">กรุณาตรวจสอบข้อมูลการรับเข้าสต็อกให้ถูกต้องก่อนยืนยันบันทึกข้อมูล:</p>

                <div class="card bg-light border p-3 rounded-3 mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                        <span id="modalColorDot" class="badge-color-dot" style="width: 14px; height: 14px;"></span>
                        <strong id="modalInkName" class="text-dark fs-6"></strong>
                    </div>
                    <div class="row g-2 small">
                        <div class="col-5 text-muted">จำนวนที่รับเข้า:</div>
                        <div class="col-7 fw-bold text-success fs-6" id="modalQuantity"></div>

                        <div class="col-5 text-muted">ยอดหลังรับเข้า:</div>
                        <div class="col-7 fw-bold text-dark fs-6" id="modalNewTotal"></div>

                        <div class="col-5 text-muted">ผู้รับเข้า/ตรวจรับ:</div>
                        <div class="col-7 fw-bold text-dark" id="modalPerson"></div>

                        <div class="col-5 text-muted">แผนกจัดซื้อ/รับผิดชอบ:</div>
                        <div class="col-7 text-dark" id="modalDepartment"></div>

                        <div class="col-5 text-muted">เลขที่ PO / เอกสาร:</div>
                        <div class="col-7 text-dark" id="modalRef"></div>

                        <div class="col-5 text-muted">วันที่รับเข้า:</div>
                        <div class="col-7 text-dark" id="modalDate"></div>
                    </div>
                </div>

                <div class="alert alert-success py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>
                    <span>ระบบจะทำการเพิ่มยอดสต็อกคงเหลือทันทีหลังจากกดยืนยัน</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับไปแก้ไข
                </button>
                <button type="button" id="confirmSubmitBtn" class="btn btn-success fw-bold px-4">
                    <i class="bi bi-save me-1"></i> ยืนยันบันทึกข้อมูล
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inkSelect = document.getElementById('inkSelect');
        const inkSearchInput = document.getElementById('inkSearchInput');
        const inkSuggestions = document.getElementById('inkSuggestions');
        const clearInkBtn = document.getElementById('clearInkBtn');
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
        const openConfirmModalBtn = document.getElementById('openConfirmModalBtn');
        const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
        const stockInForm = document.getElementById('stockInForm');

        // Modal Elements
        const confirmModalEl = document.getElementById('confirmModal');
        const confirmModal = new bootstrap.Modal(confirmModalEl);
        const modalColorDot = document.getElementById('modalColorDot');
        const modalInkName = document.getElementById('modalInkName');
        const modalQuantity = document.getElementById('modalQuantity');
        const modalNewTotal = document.getElementById('modalNewTotal');
        const modalDepartment = document.getElementById('modalDepartment');
        const modalPerson = document.getElementById('modalPerson');
        const modalDate = document.getElementById('modalDate');
        const modalRef = document.getElementById('modalRef');

        let currentStockAvailable = 0;
        let currentUnit = 'ตลับ';

        function showSuggestions() {
            inkSuggestions.classList.remove('d-none');
            inkSuggestions.style.display = 'block';
        }

        function hideSuggestions() {
            inkSuggestions.classList.add('d-none');
            inkSuggestions.style.display = 'none';
        }

        // Build search items list
        const inkItems = [];
        Array.from(inkSelect.options).forEach(opt => {
            if (!opt.value) return;
            inkItems.push({
                id: opt.value,
                code: opt.getAttribute('data-code') || '',
                brand: opt.getAttribute('data-brand') || '',
                model: opt.getAttribute('data-model') || '',
                color: opt.getAttribute('data-color') || '',
                colorCode: opt.getAttribute('data-color-code') || '#111',
                stock: parseInt(opt.getAttribute('data-stock') || 0),
                unit: opt.getAttribute('data-unit') || 'ตลับ',
                location: opt.getAttribute('data-location') || 'ไม่ได้ระบุ',
                printers: opt.getAttribute('data-printers') || '',
                text: opt.text.trim(),
                searchText: `${opt.getAttribute('data-code')} ${opt.getAttribute('data-brand')} ${opt.getAttribute('data-model')} ${opt.getAttribute('data-color')} ${opt.getAttribute('data-printers')}`.toLowerCase()
            });
        });

        function renderSuggestions(query = '') {
            if (inkItems.length === 0) {
                inkSuggestions.innerHTML = `
                    <div class="p-4 text-center">
                        <i class="bi bi-inbox text-warning fs-1 d-block mb-2"></i>
                        <div class="fw-bold text-dark mb-1">ยังไม่มีรายการหมึกพิมพ์ในระบบ</div>
                        <p class="small text-muted mb-3">เนื่องจากเชื่อมต่อ Database Cloud ใหม่ กรุณาเพิ่มรุ่นหมึกก่อนครับ</p>
                        <a href="{{ route('inks.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> คลิกเพื่อเพิ่มรุ่นหมึกพิมพ์ใหม่
                        </a>
                    </div>
                `;
                showSuggestions();
                return;
            }

            query = query.trim().toLowerCase();
            const filtered = query === '' 
                ? inkItems 
                : inkItems.filter(item => item.searchText.includes(query));

            if (filtered.length === 0) {
                inkSuggestions.innerHTML = `
                    <div class="p-3 text-muted text-center small">
                        <i class="bi bi-search me-1"></i> ไม่พบรุ่นหมึกพิมพ์ที่ตรงกับ "<strong>${escapeHtml(query)}</strong>"
                        <div class="mt-2">
                            <a href="{{ route('inks.create') }}" class="text-primary text-decoration-none fw-bold" target="_blank">
                                <i class="bi bi-plus-circle"></i> คลิกที่นี่เพื่อเพิ่มรุ่นหมึกใหม่
                            </a>
                        </div>
                    </div>`;
                showSuggestions();
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            filtered.forEach(item => {
                html += `
                    <button type="button" 
                            class="list-group-item list-group-item-action p-2 px-3 d-flex justify-content-between align-items-center" 
                            data-ink-id="${item.id}">
                        <div class="d-flex align-items-center gap-2 text-start">
                            <span class="badge-color-dot" style="width: 12px; height: 12px; background-color: ${item.colorCode};"></span>
                            <div>
                                <div class="fw-semibold text-dark">
                                    <span class="text-primary">[${escapeHtml(item.code)}]</span> ${escapeHtml(item.brand)} ${escapeHtml(item.model)} 
                                    <span class="text-muted small">(${escapeHtml(item.color)})</span>
                                </div>
                                <div class="small text-muted" style="font-size: 0.8rem;">
                                    <i class="bi bi-printer"></i> ${escapeHtml(item.printers || 'ไม่ระบุรุ่น')}
                                </div>
                            </div>
                        </div>
                        <div class="text-end ps-2">
                            <span class="badge bg-secondary-subtle text-secondary border">มีอยู่: ${item.stock} ${item.unit}</span>
                        </div>
                    </button>
                `;
            });
            html += '</div>';

            inkSuggestions.innerHTML = html;
            showSuggestions();
        }

        function selectInk(id) {
            inkSelect.value = id;
            const item = inkItems.find(i => i.id == id);
            if (item) {
                inkSearchInput.value = `[${item.code}] ${item.brand} ${item.model} (${item.color})`;
                clearInkBtn.classList.remove('d-none');
            }
            hideSuggestions();
            updateInkPreview();
        }

        function clearSelectedInk() {
            inkSelect.value = '';
            inkSearchInput.value = '';
            clearInkBtn.classList.add('d-none');
            inkInfoBox.classList.add('d-none');
            newStockPreview.textContent = '';
            currentStockAvailable = 0;
            inkSearchInput.focus();
            renderSuggestions('');
        }

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
            previewPrinters.innerHTML = '<i class="bi bi-printer me-1"></i> รองรับ: ' + escapeHtml(printers);
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

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        inkSearchInput.addEventListener('input', function () {
            renderSuggestions(this.value);
            if (this.value === '') {
                clearSelectedInk();
            }
        });

        inkSearchInput.addEventListener('focus', function () {
            renderSuggestions(this.value);
        });

        inkSearchInput.addEventListener('click', function () {
            renderSuggestions(this.value);
        });

        document.addEventListener('click', function (e) {
            if (!inkSearchInput.contains(e.target) && !inkSuggestions.contains(e.target)) {
                hideSuggestions();
            }
        });

        inkSuggestions.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-ink-id]');
            if (btn) {
                selectInk(btn.getAttribute('data-ink-id'));
            }
        });

        clearInkBtn.addEventListener('click', clearSelectedInk);
        quantityInput.addEventListener('input', calcNewTotal);

        // Open Confirmation Modal
        openConfirmModalBtn.addEventListener('click', function () {
            if (!inkSelect.value) {
                alert('กรุณาเลือกรุ่นหมึกพิมพ์ที่ต้องการรับเข้าก่อนครับ');
                inkSearchInput.focus();
                renderSuggestions('');
                return;
            }

            if (!stockInForm.checkValidity()) {
                stockInForm.reportValidity();
                return;
            }

            const selectedOpt = inkSelect.options[inkSelect.selectedIndex];
            const deptSelect = document.getElementById('departmentSelect');
            const selectedDept = deptSelect.options[deptSelect.selectedIndex];
            const personName = document.getElementById('personNameInput').value;
            const refNo = document.getElementById('referenceNoInput').value;
            const actionDate = document.getElementById('actionDateInput').value;
            const qty = parseInt(quantityInput.value || 0);

            modalColorDot.style.backgroundColor = selectedOpt.getAttribute('data-color-code') || '#111';
            modalInkName.textContent = selectedOpt.text.split(' - ปัจจุบันมี:')[0];
            modalQuantity.textContent = `+${qty} ${currentUnit}`;
            modalNewTotal.textContent = `${currentStockAvailable + qty} ${currentUnit} (เพิ่มจากเดิม ${currentStockAvailable} ${currentUnit})`;
            modalDepartment.textContent = selectedDept && selectedDept.value ? selectedDept.text.trim() : '- ไม่ระบุ -';
            modalPerson.textContent = personName;
            modalDate.textContent = actionDate;
            modalRef.textContent = refNo ? refNo : '- ไม่ระบุ -';

            confirmModal.show();
        });

        confirmSubmitBtn.addEventListener('click', function () {
            confirmSubmitBtn.disabled = true;
            confirmSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังบันทึก...';
            stockInForm.submit();
        });

        if (inkSelect.value) {
            selectInk(inkSelect.value);
        }
    });
</script>
@endpush
