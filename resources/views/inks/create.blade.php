@extends('layouts.app')

@section('title', 'เพิ่มรายการหมึกพิมพ์ใหม่')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle text-primary"></i>
                    เพิ่มรุ่นหมึกพิมพ์ใหม่เข้าสู่ระบบ
                </h4>
                <p class="text-muted small mb-0">กรอกข้อมูลรุ่นหมึก คุณสมบัติ และกำหนดจุดสั่งซื้อขั้นต่ำสำหรับการแจ้งเตือน</p>
            </div>
            <a href="{{ route('inks.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>

        <div class="card bg-white p-4">
            <form action="{{ route('inks.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <!-- Code & Name -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">รหัสหมึก (SKU / Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" placeholder="เช่น INK-HP-85A" required>
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">ชื่อหมึก / รายละเอียดสินค้า <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="เช่น HP 85A Laser Toner Cartridge (Black)" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Brand & Model -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ยี่ห้อ (Brand) <span class="text-danger">*</span></label>
                        <input list="brandList" name="brand" value="{{ old('brand') }}" class="form-control @error('brand') is-invalid @enderror" placeholder="เช่น HP, Canon, Epson" required>
                        <datalist id="brandList">
                            @foreach($brands as $b)
                                <option value="{{ $b }}">
                            @endforeach
                        </datalist>
                        @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">รุ่นหมึก (Model / Part No.) <span class="text-danger">*</span></label>
                        <input type="text" name="model" value="{{ old('model') }}" class="form-control @error('model') is-invalid @enderror" placeholder="เช่น CE285A / 85A, 003" required>
                        @error('model') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ประเภทหมึก <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach($types as $t)
                                <option value="{{ $t }}" {{ old('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Color & Color Code -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">สีของหมึก (Color) <span class="text-danger">*</span></label>
                        <select name="color" id="colorSelect" class="form-select @error('color') is-invalid @enderror" required>
                            @foreach($colors as $c)
                                <option value="{{ $c['name'] }}" data-code="{{ $c['code'] }}" {{ old('color') == $c['name'] ? 'selected' : '' }}>
                                    {{ $c['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">รหัสสีบ่งชี้ (Color Code)</label>
                        <div class="input-group">
                            <input type="color" id="colorPicker" class="form-control form-control-color" value="{{ old('color_code', '#1f2937') }}" title="เลือกสีแสดงผล">
                            <input type="text" name="color_code" id="colorCodeInput" value="{{ old('color_code', '#1f2937') }}" class="form-control" placeholder="#1f2937">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">หน่วยนับ <span class="text-danger">*</span></label>
                        <input list="unitList" name="unit" value="{{ old('unit', 'ตลับ') }}" class="form-control @error('unit') is-invalid @enderror" placeholder="ตลับ, ขวด, กล่อง" required>
                        <datalist id="unitList">
                            @foreach($units as $u)
                                <option value="{{ $u }}">
                            @endforeach
                        </datalist>
                        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Compatible Printers -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">เครื่องพิมพ์ที่รองรับ (Compatible Printers)</label>
                        <input type="text" name="compatible_printers" value="{{ old('compatible_printers') }}" class="form-control" placeholder="เช่น HP LaserJet Pro P1102, P1102w, M1132 MFP, M1212nf">
                        <div class="form-text">ระบุชื่อรุ่นเครื่องพิมพ์เพื่อให้ผู้ขอเบิกค้นหาและตรวจสอบได้ง่าย</div>
                    </div>

                    <!-- Stock & Alert -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">จำนวนคงเหลือเริ่มต้น <span class="text-danger">*</span></label>
                        <input type="number" name="current_stock" value="{{ old('current_stock', 0) }}" min="0" class="form-control @error('current_stock') is-invalid @enderror" required>
                        @error('current_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">จุดสั่งซื้อ / เตือนขั้นต่ำ <span class="text-danger">*</span></label>
                        <input type="number" name="min_stock" value="{{ old('min_stock', 2) }}" min="0" class="form-control @error('min_stock') is-invalid @enderror" required>
                        <div class="form-text">หากคงเหลือเท่ากับหรือน้อยกว่านี้ ระบบจะแจ้งเตือน</div>
                        @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ราคาต่อหน่วย (บาท)</label>
                        <input type="number" step="0.01" name="price_per_unit" value="{{ old('price_per_unit', 0) }}" min="0" class="form-control" placeholder="0.00">
                    </div>

                    <!-- Location & Notes -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">สถานที่จัดเก็บ / ชั้นวาง</label>
                        <input type="text" name="location" value="{{ old('location') }}" class="form-control" placeholder="เช่น ตู้ A-01 ชั้น 2, ห้องพัสดุ IT">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">หมายเหตุเพิ่มเติม</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" class="form-control" placeholder="เช่น หมึกแท้ศูนย์, เบิกครั้งละไม่เกิน 2 ตลับ">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('inks.index') }}" class="btn btn-light border px-4">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> บันทึกข้อมูลหมึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const colorSelect = document.getElementById('colorSelect');
        const colorPicker = document.getElementById('colorPicker');
        const colorCodeInput = document.getElementById('colorCodeInput');

        colorSelect.addEventListener('change', function () {
            const selected = colorSelect.options[colorSelect.selectedIndex];
            const code = selected.getAttribute('data-code');
            if (code) {
                colorPicker.value = code;
                colorCodeInput.value = code;
            }
        });

        colorPicker.addEventListener('input', function () {
            colorCodeInput.value = colorPicker.value;
        });

        colorCodeInput.addEventListener('input', function () {
            if (/^#[0-9A-Fa-f]{6}$/.test(colorCodeInput.value)) {
                colorPicker.value = colorCodeInput.value;
            }
        });
    });
</script>
@endpush

