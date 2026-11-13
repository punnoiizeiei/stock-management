@extends('layouts.app')

@section('title', 'แดชบอร์ดเบิกหมึกพิมพ์')

@section('content')
<div class="mb-4">
    <!-- View Switcher (Only visible for Admins) -->
    @if(auth()->check() && auth()->user()->isAdmin())
        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-white rounded-3 shadow-sm border">
            <div class="d-flex align-items-center gap-2 ps-2">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                    <i class="bi bi-shield-lock-fill me-1"></i> ผู้ดูแลระบบ (Admin)
                </span>
                <span class="text-muted small">สลับมุมมองหน้าจอเพื่อตรวจสอบการแสดงผล:</span>
            </div>
            <ul class="nav nav-pills" id="dashboardTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-1 px-3 fw-medium" id="user-view-tab" data-bs-toggle="pill" data-bs-target="#user-view" type="button" role="tab">
                        <i class="bi bi-person-fill me-1"></i> มุมมองพนักงานทั่วไป (User View)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-3 fw-medium" id="admin-view-tab" data-bs-toggle="pill" data-bs-target="#admin-view" type="button" role="tab">
                        <i class="bi bi-speedometer2 me-1"></i> สถิติผู้ดูแลคลัง (Admin View)
                    </button>
                </li>
            </ul>
        </div>
    @endif

    <div class="tab-content" id="dashboardTabContent">
        <!-- ========================================== -->
        <!-- TAB 1: USER DASHBOARD (สำหรับพนักงานทั่วไป) -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="user-view" role="tabpanel">
            
            <!-- Hero Banner: User Action Center -->
            <div class="card bg-white border-0 shadow-sm p-4 p-md-5 mb-4 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 1.25rem;">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-warning-subtle text-dark border border-warning-subtle small fw-semibold mb-3">
                            <i class="bi bi-printer-fill text-warning"></i> ระบบเบิกหมึกพิมพ์ออนไลน์ บิ๊กล็อต
                        </div>
                        <h2 class="fw-bold text-dark mb-2" style="letter-spacing: -0.5px;">
                            เช็กสต็อก & เบิกหมึกพิมพ์ไปใช้งานได้ทันที
                        </h2>
                        <p class="text-muted mb-4 fs-6">
                            พนักงานทุกคนสามารถตรวจสอบสต็อกและขอเบิกหมึกไปใช้งานได้ทันที ค้นหารุ่นหมึกที่ตรงกับเครื่องพิมพ์ของคุณ <span class="fw-semibold text-dark">โดยไม่ต้องล็อกอินหรือใส่รหัสผ่าน</span>
                        </p>

                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <a href="{{ route('transactions.stock_out') }}" class="btn btn-warning btn-lg text-dark fw-bold px-4 shadow-sm hover-scale d-inline-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-up-right fs-5"></i>
                                <span>กดเบิกหมึกพิมพ์ทันที</span>
                            </a>
                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-lg px-3 fw-medium d-inline-flex align-items-center gap-2">
                                <i class="bi bi-clock-history"></i>
                                <span>ดูประวัติ & สลิปใบเบิก</span>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Summary Card on Right -->
                    <div class="col-lg-5">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border text-center h-100">
                                    <div class="text-muted small fw-medium">หมึกพร้อมใช้งาน</div>
                                    <div class="fs-2 fw-bold text-success my-1">{{ number_format($totalStockUnits) }}</div>
                                    <div class="small text-muted">ตลับ / ขวด ในคลัง</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border text-center h-100">
                                    <div class="text-muted small fw-medium">ชนิดรุ่นทั้งหมด</div>
                                    <div class="fs-2 fw-bold text-primary my-1">{{ number_format($totalInks) }}</div>
                                    <div class="small text-muted">รุ่นที่รองรับ</div>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="p-3 rounded-3 border d-flex align-items-center gap-3 {{ $outOfStockCount > 0 ? 'bg-danger-subtle border-danger-subtle' : 'bg-success-subtle border-success-subtle' }}">
                                    <i class="bi {{ $outOfStockCount > 0 ? 'bi-exclamation-circle text-danger' : 'bi-check-circle text-success' }} fs-3"></i>
                                    <div>
                                        @if($outOfStockCount > 0)
                                            <div class="fw-bold text-danger">มีหมึกหมดสต็อก {{ $outOfStockCount }} รายการ</div>
                                            <div class="small text-muted">ฝ่ายจัดซื้อกำลังดำเนินการเติมสต็อก</div>
                                        @else
                                            <div class="fw-bold text-success">สต็อกหมึกทุกรายการพร้อมใช้งาน</div>
                                            <div class="small text-muted">สามารถเลือกเบิกได้ตามปกติ</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ink Catalog & Real-time Stock Explorer for Users -->
            <div class="card bg-white p-4 shadow-sm mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                            <i class="bi bi-grid-fill text-primary"></i>
                            รายการหมึกพิมพ์พร้อมเบิก (Ink Catalog)
                        </h5>
                        <p class="text-muted small mb-0">ค้นหารุ่นหมึก หรือพิมพ์ชื่อรุ่นเครื่องพิมพ์ของคุณเพื่อดูตลับหมึกที่รองรับ</p>
                    </div>

                    <!-- Live Quick Search -->
                    <div style="max-width: 360px; width: 100%;">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="userInkSearchInput" class="form-control bg-light border-start-0" placeholder="พิมพ์ค้นหา เช่น 003, Canon, L3110..." autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- Brand Filter Buttons -->
                <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom">
                    <button type="button" class="btn btn-sm btn-dark brand-filter-btn active" data-brand="all">ทั้งหมด ({{ $inks->count() }})</button>
                    @php
                        $brands = $inks->pluck('brand')->unique()->filter()->values();
                    @endphp
                    @foreach($brands as $b)
                        <button type="button" class="btn btn-sm btn-outline-secondary brand-filter-btn" data-brand="{{ strtolower($b) }}">
                            {{ $b }} ({{ $inks->where('brand', $b)->count() }})
                        </button>
                    @endforeach
                </div>

                <!-- Ink Cards Grid -->
                <div class="row g-3" id="inkCardsContainer">
                    @forelse($inks as $ink)
                        <div class="col-md-6 col-lg-4 ink-card-item" 
                             data-search="{{ strtolower($ink->code . ' ' . $ink->brand . ' ' . $ink->model . ' ' . $ink->color . ' ' . $ink->compatible_printers) }}" 
                             data-brand="{{ strtolower($ink->brand) }}">
                            <div class="card h-100 border p-3 rounded-3 shadow-none hover-shadow position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-color-dot" style="width: 14px; height: 14px; background-color: {{ $ink->color_code ?? '#111' }};"></span>
                                        <span class="badge bg-light text-secondary border fw-medium">{{ $ink->brand }}</span>
                                        <span class="text-muted small fw-semibold">[{{ $ink->code }}]</span>
                                    </div>
                                    @if($ink->current_stock <= 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">หมดสต็อก</span>
                                    @elseif($ink->current_stock <= $ink->min_stock)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">ใกล้หมด</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">มีของพร้อมเบิก</span>
                                    @endif
                                </div>

                                <h6 class="fw-bold text-dark mb-1">{{ $ink->model }}</h6>
                                <div class="text-muted small mb-2">สี: <strong class="text-dark">{{ $ink->color }}</strong></div>

                                <!-- Compatible Printers -->
                                <div class="small text-secondary bg-light p-2 rounded-2 mb-3" style="font-size: 0.8rem; min-height: 48px;">
                                    <i class="bi bi-printer text-muted me-1"></i> 
                                    <span class="text-dark fw-medium">{{ $ink->compatible_printers ?: 'เครื่องพิมพ์มาตรฐานทั่วไป' }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                    <div>
                                        <span class="text-muted small">คงเหลือ:</span>
                                        <strong class="fs-6 {{ $ink->current_stock <= 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $ink->current_stock }}
                                        </strong>
                                        <span class="text-muted small">{{ $ink->unit }}</span>
                                    </div>

                                    @if($ink->current_stock > 0)
                                        <a href="{{ route('transactions.stock_out', ['ink_id' => $ink->id]) }}" class="btn btn-sm btn-warning text-dark fw-bold px-3">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> เบิกรุ่นนี้
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-light border text-muted px-3" disabled>
                                            สินค้าหมด
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 py-5 text-center text-muted">
                            <i class="bi bi-inbox fs-1 text-secondary d-block mb-3"></i>
                            <h6 class="fw-bold text-dark">ยังไม่มีรายการหมึกพิมพ์ในระบบ</h6>
                            <p class="small text-muted mb-3">แอดมินสามารถคลิกเพิ่มข้อมูลตลับหมึกและสต็อกเข้าสู่ระบบได้ทันที</p>
                            @if(auth()->check() && auth()->user()->isAdmin())
                                <a href="{{ route('inks.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> เพิ่มรายการหมึกพิมพ์
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <!-- Empty search result alert -->
                <div id="noMatchAlert" class="text-center py-5 d-none">
                    <i class="bi bi-search text-muted fs-1 d-block mb-2"></i>
                    <h6 class="fw-bold text-dark">ไม่พบหมึกพิมพ์ที่ตรงกับคำค้นหา</h6>
                    <p class="text-muted small">ลองค้นหาด้วยคำอื่น เช่น รหัสหมึก หรือชื่อยี่ห้อ (EPSON, Canon, Brother, HP)</p>
                </div>
            </div>

            <!-- 3 Easy Steps & Recent Requisitions Row -->
            <div class="row g-4">
                <!-- 3 Easy Steps Card -->
                <div class="col-lg-5">
                    <div class="card bg-white p-4 h-100 shadow-sm">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2 text-dark">
                            <i class="bi bi-info-circle-fill text-primary"></i> 3 ขั้นตอนง่ายๆ ในการเบิกหมึกพิมพ์
                        </h6>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="badge rounded-circle bg-primary-subtle text-primary p-2 fs-6 flex-shrink-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    1
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">ตรวจสอบรุ่นเครื่องพิมพ์</div>
                                    <small class="text-muted">ดูชื่อรุ่นเครื่องพิมพ์ของคุณ เช่น EPSON L3150 แล้วเลือกรหัสหมึกที่ตรงกัน</small>
                                </div>
                            </div>

                            <div class="d-flex gap-3 align-items-start">
                                <div class="badge rounded-circle bg-warning-subtle text-dark p-2 fs-6 flex-shrink-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    2
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">กดปุ่มเบิกหมึก & กรอกชื่อ</div>
                                    <small class="text-muted">ระบุจำนวนที่ต้องการ, แผนก/ฝ่าย, และชื่อผู้เบิก (ไม่ต้องใช้รหัสผ่าน)</small>
                                </div>
                            </div>

                            <div class="d-flex gap-3 align-items-start">
                                <div class="badge rounded-circle bg-success-subtle text-success p-2 fs-6 flex-shrink-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    3
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">ตรวจสอบ Popup & รับหมึก</div>
                                    <small class="text-muted">ตรวจสอบความถูกต้องในหน้าต่าง Popup ยืนยัน และสามารถพิมพ์หรือบันทึกใบสลิปไว้ได้</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-center">
                            <a href="{{ route('transactions.stock_out') }}" class="btn btn-warning text-dark fw-bold w-100">
                                <i class="bi bi-box-arrow-up-right me-1"></i> เริ่มต้นทำรายการเบิกหมึกเลย
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Requisitions Table for Users -->
                <div class="col-lg-7">
                    <div class="card bg-white h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history text-primary"></i>
                                รายการเบิกหมึกล่าสุด
                            </h6>
                            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.8rem;">
                                ดูประวัติทั้งหมด
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>วันที่</th>
                                        <th>รุ่นหมึก</th>
                                        <th class="text-center">จำนวน</th>
                                        <th>ผู้ขอเบิก / แผนก</th>
                                        <th class="text-center">สลิป</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTransactions->where('type', 'out')->take(6) as $tx)
                                        <tr>
                                            <td>
                                                <div class="small fw-semibold text-dark">{{ $tx->action_date->format('d/m/Y') }}</div>
                                                <span class="text-muted" style="font-size: 0.72rem;">{{ $tx->transaction_code }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge-color-dot" style="width: 10px; height: 10px; background-color: {{ $tx->ink->color_code ?? '#333' }};"></span>
                                                    <span class="fw-bold text-dark">{{ $tx->ink->model ?? '-' }}</span>
                                                </div>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $tx->ink->brand ?? '' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning-subtle text-dark border fw-bold px-2 py-1">
                                                    {{ $tx->quantity }} {{ $tx->ink->unit ?? 'ตลับ' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small fw-medium text-dark">{{ $tx->person_name }}</div>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $tx->department_name ?? '-' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('transactions.show', $tx) }}" class="btn btn-sm btn-light border py-1 px-2 text-primary" title="ดูสลิป / พิมพ์">
                                                    <i class="bi bi-receipt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">ยังไม่มีรายการเบิกหมึก</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 2: ADMIN DASHBOARD (เฉพาะเมื่อแอดมินดูสถิติ) -->
        <!-- ========================================== -->
        @if(auth()->check() && auth()->user()->isAdmin())
            <div class="tab-pane fade" id="admin-view" role="tabpanel">
                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="card h-100 p-3 bg-white border-start border-primary border-4 shadow-sm">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-medium">รายการหมึกทั้งหมด</span>
                                    <h3 class="fw-bold mb-0 mt-1">{{ number_format($totalInks) }}</h3>
                                    <small class="text-muted">ชนิด/รุ่น ในระบบ</small>
                                </div>
                                <div class="stat-card icon-box bg-primary-subtle text-primary">
                                    <i class="bi bi-printer"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="card h-100 p-3 bg-white border-start border-success border-4 shadow-sm">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-medium">ยอดคงเหลือรวม</span>
                                    <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($totalStockUnits) }}</h3>
                                    <small class="text-muted">ตลับ / ขวด พร้อมใช้งาน</small>
                                </div>
                                <div class="stat-card icon-box bg-success-subtle text-success">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="card h-100 p-3 bg-white border-start border-warning border-4 shadow-sm">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-medium">หมึกใกล้หมด (เตือน)</span>
                                    <h3 class="fw-bold text-warning mb-0 mt-1">{{ number_format($lowStockCount) }}</h3>
                                    <small class="text-muted">&le; จุดสั่งซื้อขั้นต่ำ</small>
                                </div>
                                <div class="stat-card icon-box bg-warning-subtle text-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="card h-100 p-3 bg-white border-start border-danger border-4 shadow-sm">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-medium">หมึกหมดสต็อก</span>
                                    <h3 class="fw-bold text-danger mb-0 mt-1">{{ number_format($outOfStockCount) }}</h3>
                                    <small class="text-muted">ต้องสั่งซื้อด่วน (0 หน่วย)</small>
                                </div>
                                <div class="stat-card icon-box bg-danger-subtle text-danger">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Performance Banner -->
                <div class="card bg-white p-3 p-md-4 mb-4 shadow-sm">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-6">
                            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar2-week text-primary"></i>
                                สรุปยอดความเคลื่อนไหวประจำเดือน {{ \Carbon\Carbon::now()->locale('th')->translatedFormat('F Y') }}
                            </h5>
                            <p class="text-muted small mb-0">แอดมินสามารถรับหมึกเข้าคลังและบริหารจัดการสต็อกได้จากจุดนี้</p>
                        </div>
                        <div class="col-lg-6 d-flex flex-wrap justify-content-lg-end gap-3">
                            <div class="bg-light px-3 py-2 rounded-3 border d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-down-circle text-success fs-4"></i>
                                <div>
                                    <div class="text-muted small">รับเข้าเดือนนี้</div>
                                    <div class="fw-bold text-success fs-6">+{{ number_format($monthlyStockIn) }} หน่วย</div>
                                </div>
                            </div>
                            <div class="bg-light px-3 py-2 rounded-3 border d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-up-circle text-primary fs-4"></i>
                                <div>
                                    <div class="text-muted small">เบิกจ่ายเดือนนี้</div>
                                    <div class="fw-bold text-primary fs-6">-{{ number_format($monthlyStockOut) }} หน่วย</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('transactions.stock_in') }}" class="btn btn-success fw-semibold">
                                    <i class="bi bi-box-arrow-in-down me-1"></i> รับหมึกเข้า
                                </a>
                                <a href="{{ route('inks.create') }}" class="btn btn-primary fw-semibold">
                                    <i class="bi bi-plus-circle me-1"></i> เพิ่มรุ่นหมึก
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card bg-white p-3 p-md-4 h-100 shadow-sm">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-pie-chart text-primary me-1"></i> สต็อกคงเหลือแยกตามยี่ห้อ (Brand)
                            </h6>
                            <div style="height: 250px; position: relative;">
                                <canvas id="brandChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card bg-white p-3 p-md-4 h-100 shadow-sm">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-bar-chart-fill text-success me-1"></i> ปริมาณการเบิกหมึกแยกตามแผนก
                            </h6>
                            <div style="height: 250px; position: relative;">
                                <canvas id="deptChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Box -->
                <div class="card bg-white shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-danger d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            รายการที่ต้องสั่งซื้อ / หมึกใกล้หมด
                        </h6>
                        <span class="badge bg-danger rounded-pill">{{ $alertInks->count() }} รายการ</span>
                    </div>
                    <div class="card-body p-0">
                        @if($alertInks->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                                ระดับสต็อกหมึกทุกรายการอยู่ในเกณฑ์ปกติ
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($alertInks as $item)
                                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge-color-dot" style="background-color: {{ $item->color_code }}"></span>
                                                <span class="fw-semibold text-dark">{{ $item->model }}</span>
                                                <span class="badge bg-light text-secondary border">{{ $item->brand }}</span>
                                            </div>
                                            <small class="text-muted d-block mt-1">{{ $item->name }}</small>
                                            <small class="text-muted">
                                                คงเหลือ: 
                                                <strong class="{{ $item->current_stock <= 0 ? 'text-danger' : 'text-warning' }}">
                                                    {{ $item->current_stock }} {{ $item->unit }}
                                                </strong> 
                                                (ขั้นต่ำ: {{ $item->min_stock }} {{ $item->unit }})
                                            </small>
                                        </div>
                                        <div>
                                            <a href="{{ route('transactions.stock_in', ['ink_id' => $item->id]) }}" class="btn btn-sm btn-outline-success py-1 px-2 text-nowrap">
                                                <i class="bi bi-plus-circle"></i> รับเข้าสต็อก
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if(auth()->check() && auth()->user()->isAdmin())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Brand Distribution Chart
            const brandData = @json($brandDistribution);
            const brandCtx = document.getElementById('brandChart');
            if (brandCtx) {
                new Chart(brandCtx.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: Object.keys(brandData),
                        datasets: [{
                            data: Object.values(brandData),
                            backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#6f42c1', '#d63384'],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { family: 'Prompt' } }
                            }
                        }
                    }
                });
            }

            // Department Requisition Chart
            const deptData = @json($deptRequisitions);
            const deptCtx = document.getElementById('deptChart');
            if (deptCtx) {
                new Chart(deptCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(deptData),
                        datasets: [{
                            label: 'จำนวนเบิก (ตลับ/ขวด)',
                            data: Object.values(deptData),
                            backgroundColor: '#0d6efd',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { family: 'Prompt' } }
                            },
                            x: {
                                ticks: { font: { family: 'Prompt' } }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Quick Live Search & Filter for User Ink Catalog Cards
        const searchInput = document.getElementById('userInkSearchInput');
        const filterBtns = document.querySelectorAll('.brand-filter-btn');
        const cards = document.querySelectorAll('.ink-card-item');
        const noMatchAlert = document.getElementById('noMatchAlert');

        let currentBrand = 'all';
        let currentQuery = '';

        function filterCards() {
            let visibleCount = 0;

            cards.forEach(card => {
                const searchData = card.getAttribute('data-search') || '';
                const cardBrand = card.getAttribute('data-brand') || '';

                const matchesQuery = currentQuery === '' || searchData.includes(currentQuery);
                const matchesBrand = currentBrand === 'all' || cardBrand === currentBrand;

                if (matchesQuery && matchesBrand) {
                    card.classList.remove('d-none');
                    visibleCount++;
                } else {
                    card.classList.add('d-none');
                }
            });

            if (noMatchAlert) {
                if (visibleCount === 0 && cards.length > 0) {
                    noMatchAlert.classList.remove('d-none');
                } else {
                    noMatchAlert.classList.add('d-none');
                }
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentQuery = this.value.trim().toLowerCase();
                filterCards();
            });
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                filterBtns.forEach(b => {
                    b.classList.remove('btn-dark', 'active');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-dark', 'active');

                currentBrand = this.getAttribute('data-brand') || 'all';
                filterCards();
            });
        });
    });
</script>
@endpush
