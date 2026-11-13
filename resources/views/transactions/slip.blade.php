@extends('layouts.app')

@section('title', 'ใบสำคัญการ' . ($transaction->type === 'in' ? 'รับหมึก' : 'เบิกหมึก') . ' - ' . $transaction->transaction_code)

@push('styles')
<style>
    @media print {
        nav, footer, .no-print, .btn-print-action {
            display: none !important;
        }
        body {
            background-color: #fff !important;
            color: #000 !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .slip-container {
            border: none !important;
            box-shadow: none !important;
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Top Action Buttons (Hidden on Print) -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ย้อนกลับไปประวัติ
            </a>
            <div class="d-flex gap-2">
                <button type="button" onclick="window.print()" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-printer"></i> พิมพ์เอกสาร (Print)
                </button>
            </div>
        </div>

        <!-- The Printable Slip -->
        <div class="card bg-white p-4 p-md-5 slip-container shadow-sm border">
            <!-- Header -->
            <div class="row pb-3 mb-4 border-bottom align-items-center">
                <div class="col-8">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="p-2 bg-dark text-white rounded-2 d-inline-flex">
                            <i class="bi bi-printer-fill fs-5"></i>
                        </span>
                        <div>
                            <h4 class="fw-bold mb-0">ระบบจัดการสต็อกและเบิก-รับหมึกพิมพ์</h4>
                            <small class="text-muted">Ink Stock & Inventory Management System</small>
                        </div>
                    </div>
                </div>
                <div class="col-4 text-end">
                    <span class="badge {{ $transaction->type === 'in' ? 'bg-success' : 'bg-primary' }} fs-6 px-3 py-2">
                        {{ $transaction->type === 'in' ? 'ใบรับหมึกเข้าสต็อก' : 'ใบเบิกพัสดุหมึกพิมพ์' }}
                    </span>
                    <div class="fw-bold text-dark mt-2 font-monospace">{{ $transaction->transaction_code }}</div>
                    <small class="text-muted">วันที่: {{ $transaction->action_date->format('d/m/Y') }}</small>
                </div>
            </div>

            <!-- Details Info Grid -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                <div class="col-6 col-sm-4">
                    <small class="text-muted d-block">ประเภทรายการ</small>
                    <strong class="{{ $transaction->type === 'in' ? 'text-success' : 'text-primary' }}">
                        {{ $transaction->type_label }}
                    </strong>
                </div>
                <div class="col-6 col-sm-4">
                    <small class="text-muted d-block">{{ $transaction->type === 'in' ? 'ผู้ตรวจรับพัสดุ' : 'ผู้ขอเบิก' }}</small>
                    <strong class="text-dark">{{ $transaction->person_name }}</strong>
                </div>
                <div class="col-6 col-sm-4">
                    <small class="text-muted d-block">แผนก / ฝ่าย</small>
                    <strong class="text-dark">{{ $transaction->department_name ?: '-' }}</strong>
                </div>

                @if($transaction->printer_target)
                    <div class="col-12 col-sm-8">
                        <small class="text-muted d-block">เครื่องพิมพ์ที่นำไปใช้ / จุดติดตั้ง</small>
                        <strong class="text-dark"><i class="bi bi-printer me-1"></i> {{ $transaction->printer_target }}</strong>
                    </div>
                @endif

                @if($transaction->reference_no)
                    <div class="col-6 col-sm-4">
                        <small class="text-muted d-block">เลขที่เอกสารอ้างอิง (PO / ใบขอเบิก)</small>
                        <strong class="text-dark">{{ $transaction->reference_no }}</strong>
                    </div>
                @endif
            </div>

            <!-- Ink Item Detail Table -->
            <h6 class="fw-bold text-dark mb-2">รายละเอียดรายการหมึก</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>รหัส / รุ่นหมึกพิมพ์</th>
                            <th>ยี่ห้อ & สี</th>
                            <th class="text-center" style="width: 120px;">จำนวน</th>
                            <th class="text-center" style="width: 140px;">สถานะสต็อก</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">1</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $transaction->ink->model }}</div>
                                <div class="small text-muted">{{ $transaction->ink->name }}</div>
                                <div class="small text-muted font-monospace">SKU: {{ $transaction->ink->code }}</div>
                            </td>
                            <td>
                                <div>{{ $transaction->ink->brand }}</div>
                                <div class="small d-flex align-items-center gap-1">
                                    <span class="badge-color-dot" style="background-color: {{ $transaction->ink->color_code }}"></span>
                                    {{ $transaction->ink->color }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fs-5 fw-bold {{ $transaction->type === 'in' ? 'text-success' : 'text-primary' }}">
                                    {{ $transaction->quantity }}
                                </span>
                                <span class="text-muted">{{ $transaction->ink->unit }}</span>
                            </td>
                            <td class="text-center small">
                                <div>ก่อนทำรายการ: {{ $transaction->stock_before }} {{ $transaction->ink->unit }}</div>
                                <div><strong>คงเหลือล่าสุด: {{ $transaction->stock_after }} {{ $transaction->ink->unit }}</strong></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Notes Section -->
            @if($transaction->notes)
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <small class="text-muted d-block fw-semibold mb-1">หมายเหตุ / วัตถุประสงค์:</small>
                    <div class="text-dark">{{ $transaction->notes }}</div>
                </div>
            @endif

            <!-- Signatures Section for Official Printing -->
            <div class="row pt-5 mt-4 text-center g-4">
                <div class="col-4">
                    <div class="border-bottom pb-4 mb-2"></div>
                    <div class="fw-semibold small">ลงชื่อ .....................................................</div>
                    <div class="text-muted small">({{ $transaction->person_name }})</div>
                    <div class="text-muted small mt-1">{{ $transaction->type === 'in' ? 'ผู้ตรวจรับพัสดุ' : 'ผู้ขอเบิกพัสดุ' }}</div>
                </div>
                <div class="col-4">
                    <div class="border-bottom pb-4 mb-2"></div>
                    <div class="fw-semibold small">ลงชื่อ .....................................................</div>
                    <div class="text-muted small">(เจ้าหน้าที่พัสดุ / คลังหมึก)</div>
                    <div class="text-muted small mt-1">{{ $transaction->type === 'in' ? 'ผู้บันทึกรับเข้า' : 'ผู้จ่ายพัสดุ' }}</div>
                </div>
                <div class="col-4">
                    <div class="border-bottom pb-4 mb-2"></div>
                    <div class="fw-semibold small">ลงชื่อ .....................................................</div>
                    <div class="text-muted small">(หัวหน้างาน / ผู้มีอำนาจอนุมัติ)</div>
                    <div class="text-muted small mt-1">ผู้อนุมัติ</div>
                </div>
            </div>

            <div class="text-center text-muted small mt-5 pt-3 border-top">
                พิมพ์เมื่อ: {{ \Carbon\Carbon::now()->locale('th')->translatedFormat('d F Y H:i น.') }} &bull; เอกสารสร้างจากระบบจัดการสต็อกหมึกพิมพ์
            </div>
        </div>
    </div>
</div>
@endsection

