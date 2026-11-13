@extends('layouts.app')

@section('title', 'จัดการข้อมูลแผนก/ฝ่าย')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-building text-primary"></i>
            จัดการแผนก / ฝ่าย (Departments)
        </h4>
        <p class="text-muted small mb-0">กำหนดรายชื่อแผนกเพื่อใช้ในการจัดกลุ่มและบันทึกประวัติการเบิกหมึกพิมพ์</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addDeptModal">
            <i class="bi bi-plus-lg"></i> เพิ่มแผนกใหม่
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card bg-white shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">#</th>
                            <th style="width: 140px;">รหัสแผนก</th>
                            <th>ชื่อแผนก / ฝ่าย</th>
                            <th>ผู้ประสานงานหลัก</th>
                            <th class="text-center" style="width: 150px;">จำนวนครั้งที่เบิกหมึก</th>
                            <th class="text-end" style="width: 140px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $dept)
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $dept->code ?: '-' }}</span>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $dept->name }}</strong>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $dept->contact_person ?: '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-primary border fs-6">
                                        {{ $dept->transactions_count }} รายการ
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-btn" 
                                            data-id="{{ $dept->id }}"
                                            data-name="{{ $dept->name }}"
                                            data-code="{{ $dept->code }}"
                                            data-contact="{{ $dept->contact_person }}"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editDeptModal" 
                                            title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('คุณต้องการลบแผนกนี้ใช่หรือไม่?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ" {{ $dept->transactions_count > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีข้อมูลแผนก</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($departments->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $departments->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('departments.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addDeptModalLabel"><i class="bi bi-building-add text-primary me-1"></i> เพิ่มแผนกใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อแผนก / ฝ่าย <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="เช่น แผนกบัญชีและการเงิน" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">รหัสย่อแผนก</label>
                        <input type="text" name="code" class="form-control" placeholder="เช่น ACC, IT, HR">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ผู้ประสานงานหลัก</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="ชื่อ-นามสกุล ผู้ติดต่อประจำแผนก">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="bi bi-save me-1"></i> บันทึกข้อมูล</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDeptModal" tabindex="-1" aria-labelledby="editDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editDeptForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editDeptModalLabel"><i class="bi bi-pencil-square text-primary me-1"></i> แก้ไขข้อมูลแผนก</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อแผนก / ฝ่าย <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">รหัสย่อแผนก</label>
                        <input type="text" name="code" id="editCode" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ผู้ประสานงานหลัก</label>
                        <input type="text" name="contact_person" id="editContact" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="bi bi-save me-1"></i> บันทึกการแก้ไข</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-btn');
        const editDeptForm = document.getElementById('editDeptForm');
        const editName = document.getElementById('editName');
        const editCode = document.getElementById('editCode');
        const editContact = document.getElementById('editContact');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const code = this.getAttribute('data-code');
                const contact = this.getAttribute('data-contact');

                editDeptForm.action = `/departments/${id}`;
                editName.value = name;
                editCode.value = code || '';
                editContact.value = contact || '';
            });
        });
    });
</script>
@endpush

