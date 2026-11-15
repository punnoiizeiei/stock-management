@extends('layouts.app')

@section('title', 'สำรองและกู้คืนข้อมูล (Backup & Restore)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <span class="p-2 bg-primary-subtle text-primary rounded-3 d-inline-flex">
                        <i class="bi bi-cloud-arrow-down fs-5"></i>
                    </span>
                    สำรองและกู้คืนข้อมูล (Backup & Restore)
                </h4>
                <p class="text-muted small mb-0">ดาวน์โหลดสำรองข้อมูลเก็บไว้ในคอมพิวเตอร์ของคุณ หรือนำเข้าข้อมูลเดิมกลับคืนสู่ระบบ</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> กลับสู่แดชบอร์ด
            </a>
        </div>

        <!-- Current Database Summary -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card p-3 bg-white border shadow-sm text-center">
                    <div class="text-muted small">รุ่นหมึกในระบบ</div>
                    <div class="fs-3 fw-bold text-primary mt-1">{{ number_format($totalInks) }}</div>
                    <small class="text-muted">รายการ</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 bg-white border shadow-sm text-center">
                    <div class="text-muted small">ประวัติการเบิก-รับ</div>
                    <div class="fs-3 fw-bold text-success mt-1">{{ number_format($totalTransactions) }}</div>
                    <small class="text-muted">รายการ</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 bg-white border shadow-sm text-center">
                    <div class="text-muted small">แผนก/ฝ่าย</div>
                    <div class="fs-3 fw-bold text-info mt-1">{{ number_format($totalDepartments) }}</div>
                    <small class="text-muted">แผนก</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 bg-white border shadow-sm text-center">
                    <div class="text-muted small">บัญชีผู้ดูแล</div>
                    <div class="fs-3 fw-bold text-secondary mt-1">{{ number_format($totalUsers) }}</div>
                    <small class="text-muted">ผู้ใช้</small>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- 1. Download Backup Section -->
            <div class="col-lg-7">
                <div class="card bg-white p-4 shadow-sm h-100">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2 text-dark">
                        <i class="bi bi-download text-success"></i>
                        1. ดาวน์โหลดสำรองข้อมูล (Export)
                    </h5>
                    <p class="text-muted small mb-4">
                        กดดาวน์โหลดไฟล์เก็บไว้ในคอมพิวเตอร์ของคุณเป็นประจำ เพื่อความปลอดภัยและป้องกันข้อมูลสูญหาย
                    </p>

                    <div class="d-flex flex-column gap-3">
                        <!-- Full JSON Backup -->
                        <div class="p-3 bg-light rounded-3 border d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-filetype-json text-primary fs-5"></i>
                                    สำรองข้อมูลทั้งระบบ (Full System Backup)
                                </div>
                                <small class="text-muted">รวมข้อมูลตลับหมึก, แผนก, และประวัติการเบิก-รับทั้งหมดในไฟล์เดียว (ใช้สำหรับกู้คืนได้ทันที)</small>
                            </div>
                            <a href="{{ route('backup.export_json') }}" class="btn btn-primary fw-semibold px-3 text-nowrap">
                                <i class="bi bi-download me-1"></i> ดาวน์โหลด .JSON
                            </a>
                        </div>

                        <!-- Inks CSV for Excel -->
                        <div class="p-3 bg-light rounded-3 border d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-excel text-success fs-5"></i>
                                    ส่งออกรายการหมึก & สต็อกคงเหลือ
                                </div>
                                <small class="text-muted">ไฟล์ Excel / CSV รองรับภาษาไทยสมบูรณ์ เปิดดูสต็อกและราคาได้ใน Microsoft Excel</small>
                            </div>
                            <a href="{{ route('backup.export_inks_csv') }}" class="btn btn-outline-success fw-semibold px-3 text-nowrap">
                                <i class="bi bi-file-spreadsheet me-1"></i> ส่งออก .CSV
                            </a>
                        </div>

                        <!-- Transactions CSV for Excel -->
                        <div class="p-3 bg-light rounded-3 border d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history text-info fs-5"></i>
                                    ส่งออกประวัติการเบิก-รับหมึก
                                </div>
                                <small class="text-muted">ไฟล์ Excel / CSV ประวัติย้อนหลัง สำหรับฝ่ายบัญชีและตรวจสอบการใช้งาน</small>
                            </div>
                            <a href="{{ route('backup.export_transactions_csv') }}" class="btn btn-outline-info text-dark fw-semibold px-3 text-nowrap">
                                <i class="bi bi-file-spreadsheet me-1"></i> ส่งออก .CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Restore Backup Section -->
            <div class="col-lg-5">
                <div class="card bg-white p-4 shadow-sm h-100">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2 text-dark">
                        <i class="bi bi-upload text-warning"></i>
                        2. กู้คืนข้อมูล (Restore Backup)
                    </h5>
                    <p class="text-muted small mb-3">
                        เลือกไฟล์สำรองข้อมูล (<code>.json</code>) ที่เคยดาวน์โหลดไว้จากระบบ เพื่อนำข้อมูลทั้งหมดกลับคืนมา
                    </p>

                    <form action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" id="restoreForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">เลือกไฟล์สำรอง (.json) <span class="text-danger">*</span></label>
                            <input type="file" name="backup_file" accept=".json,.txt" class="form-control" required>
                        </div>

                        <div class="form-check bg-light p-3 rounded-2 border mb-3">
                            <input class="form-check-input" type="checkbox" name="clean_restore" value="1" id="cleanRestoreCheck">
                            <label class="form-check-label small text-dark" for="cleanRestoreCheck">
                                <strong>ล้างข้อมูลเดิมก่อนกู้คืน</strong> (แนะนำหากต้องการให้ข้อมูลเหมือนในไฟล์สำรอง 100%)
                            </label>
                        </div>

                        <div class="alert alert-warning py-2 px-3 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> ข้อมูลตลับหมึกและประวัติจะถูกเขียนทับหรือเพิ่มจากไฟล์สำรอง
                        </div>

                        <button type="button" class="btn btn-warning text-dark fw-bold w-100" data-bs-toggle="modal" data-bs-target="#confirmRestoreModal">
                            <i class="bi bi-arrow-repeat me-1"></i> เริ่มต้นกู้คืนข้อมูล
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PostgreSQL Direct Backup Guide -->
        <div class="card bg-white p-4 shadow-sm">
            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2 text-dark">
                <i class="bi bi-shield-check text-primary"></i>
                การสำรองข้อมูลระดับฐานข้อมูล PostgreSQL บน Render Cloud
            </h6>
            <p class="text-muted small mb-0">
                ฐานข้อมูล PostgreSQL บน Render มีการบันทึกข้อมูลอย่างปลอดภัยในตัวอยู่แล้ว และคุณสามารถกดดาวน์โหลดไฟล์ <strong>.JSON</strong> ด้านบนเก็บไว้ในเครื่องได้ตลอดเวลา ซึ่งเป็นวิธีที่ง่าย สะดวก และสามารถนำกลับมากดกู้คืนได้เองในคลิกเดียวครับ!
            </p>
        </div>
    </div>
</div>

<!-- Modal: Confirm Restore -->
<div class="modal fade" id="confirmRestoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning-subtle text-dark border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                    ยืนยันการกู้คืนข้อมูลสำรอง
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-dark small mb-3">
                    คุณแน่ใจหรือไม่ว่าต้องการนำเข้าข้อมูลจากไฟล์สำรองนี้? ระบบจะทำการอัปเดตข้อมูลตลับหมึกและประวัติการเบิก-รับตามข้อมูลในไฟล์สำรอง
                </p>
                <div class="alert alert-secondary py-2 px-3 small mb-0">
                    กรุณาตรวจสอบให้แน่ใจว่าเป็นไฟล์ <code>.json</code> ที่ถูกต้อง
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-warning text-dark fw-bold px-4" onclick="document.getElementById('restoreForm').submit();">
                    <i class="bi bi-check2-circle me-1"></i> ยืนยันการกู้คืน
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
