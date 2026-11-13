@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งานระบบ')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary"></i>
            จัดการผู้ใช้งานระบบ (User & Admin Management)
        </h4>
        <p class="text-muted small mb-0">กำหนดสิทธิ์การใช้งาน (Admin / User) และแผนกสังกัดสำหรับผู้ใช้งานแต่ละท่าน</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill"></i> เพิ่มผู้ใช้งานใหม่
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card bg-white p-3 mb-4">
    <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="ค้นหา ชื่อ, อีเมล...">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">-- สิทธิ์ทั้งหมด --</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>👑 ผู้ดูแลระบบ (Admin)</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>👤 ผู้ใช้งานทั่วไป (User)</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="department_id" class="form-select">
                <option value="">-- ทุกแผนก --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-dark w-100" title="กรองข้อมูล">
                <i class="bi bi-funnel"></i>
            </button>
            @if(request()->hasAny(['search', 'role', 'department_id']))
                <a href="{{ route('users.index') }}" class="btn btn-light border" title="ล้าง">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="card bg-white shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;" class="text-center">#</th>
                    <th>ชื่อผู้ใช้งาน</th>
                    <th>อีเมล</th>
                    <th class="text-center" style="width: 140px;">ระดับสิทธิ์ (Role)</th>
                    <th>แผนก / ฝ่ายสังกัด</th>
                    <th class="text-center" style="width: 120px;">ประวัติทำรายการ</th>
                    <th class="text-end" style="width: 130px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="{{ $user->id === auth()->id() ? 'table-primary bg-opacity-10' : '' }}">
                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="badge bg-primary text-white small" style="font-size: 0.7rem;">บัญชีคุณ</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-muted font-monospace small">{{ $user->email }}</span>
                        </td>
                        <td class="text-center">
                            @if($user->isAdmin())
                                <span class="badge bg-danger py-1 px-2 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-shield-lock-fill"></i> Admin
                                </span>
                            @else
                                <span class="badge bg-secondary py-1 px-2 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-person-fill"></i> User
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-building me-1"></i>{{ $user->department?->name ?? 'ไม่ระบุแผนก' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-primary border">
                                {{ $user->transactions_count }} รายการ
                            </span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary edit-user-btn"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-role="{{ $user->role }}"
                                    data-department="{{ $user->department_id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editUserModal"
                                    title="แก้ไขข้อมูล">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบบัญชีผู้ใช้นี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบผู้ใช้" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลผู้ใช้งานตามเงื่อนไข</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill text-primary me-1"></i> เพิ่มผู้ใช้งานใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อ - นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="เช่น วิชัย สมบูรณ์" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="user@company.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">รหัสผ่านเริ่มต้น <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">สิทธิ์การใช้งาน (Role) <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="user" selected>👤 ผู้ใช้งานทั่วไป (User - เบิกหมึก/ดูสต็อก)</option>
                            <option value="admin">👑 ผู้ดูแลระบบ (Admin - จัดการทุกอย่าง)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">แผนก / ฝ่าย สังกัด</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- ไม่ระบุแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="bi bi-save me-1"></i> บันทึกผู้ใช้</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editUserForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-1"></i> แก้ไขข้อมูลผู้ใช้งาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อ - นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUserName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">เปลี่ยนรหัสผ่านใหม่ (เว้นว่างไว้หากไม่เปลี่ยน)</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">สิทธิ์การใช้งาน (Role) <span class="text-danger">*</span></label>
                        <select name="role" id="editUserRole" class="form-select" required>
                            <option value="user">👤 ผู้ใช้งานทั่วไป (User - เบิกหมึก/ดูสต็อก)</option>
                            <option value="admin">👑 ผู้ดูแลระบบ (Admin - จัดการทุกอย่าง)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">แผนก / ฝ่าย สังกัด</label>
                        <select name="department_id" id="editUserDept" class="form-select">
                            <option value="">-- ไม่ระบุแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
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
        const editButtons = document.querySelectorAll('.edit-user-btn');
        const editUserForm = document.getElementById('editUserForm');
        const editUserName = document.getElementById('editUserName');
        const editUserEmail = document.getElementById('editUserEmail');
        const editUserRole = document.getElementById('editUserRole');
        const editUserDept = document.getElementById('editUserDept');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const email = this.getAttribute('data-email');
                const role = this.getAttribute('data-role');
                const dept = this.getAttribute('data-department');

                editUserForm.action = `/users/${id}`;
                editUserName.value = name;
                editUserEmail.value = email;
                editUserRole.value = role;
                editUserDept.value = dept || '';
            });
        });
    });
</script>
@endpush

