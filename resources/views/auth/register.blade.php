<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนผู้ใช้งาน - ระบบจัดการสต็อกและเบิก-รับหมึกพิมพ์</title>

    <!-- Google Fonts: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
        }

        .form-control, .form-select {
            border-radius: 0.6rem;
            padding: 0.65rem 0.9rem;
        }

        .btn {
            border-radius: 0.6rem;
            padding: 0.65rem 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="auth-card card bg-white">
    <div class="card-body p-4 p-sm-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex p-3 bg-primary text-white rounded-4 shadow-sm mb-3">
                <i class="bi bi-person-plus-fill fs-2"></i>
            </div>
            <h4 class="fw-bold mb-1 text-dark">ลงทะเบียนผู้ใช้งานใหม่</h4>
            <p class="text-muted small mb-0">สร้างบัญชีสำหรับขอเบิกหมึกพิมพ์ประจำแผนกของคุณ</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show small rounded-3" role="alert">
                <i class="bi bi-x-circle me-1"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">ชื่อ - นามสกุล <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control border-start-0" placeholder="เช่น กัญญา ใจดี" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">อีเมลสำหรับเข้าสู่ระบบ <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control border-start-0" placeholder="your@email.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">แผนก / ฝ่าย สังกัด</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-building text-muted"></i></span>
                    <select name="department_id" class="form-select border-start-0">
                        <option value="">-- เลือกแผนกที่สังกัด --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">รหัสผ่าน (อย่างน้อย 6 ตัวอักษร) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small text-muted">ยืนยันรหัสผ่านอีกครั้ง <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                    <input type="password" name="password_confirmation" class="form-control border-start-0" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3 shadow-sm">
                <i class="bi bi-check-circle me-1"></i> ยืนยันการลงทะเบียน
            </button>
        </form>

        <div class="text-center mt-3">
            <span class="small text-muted">มีบัญชีผู้ใช้งานอยู่แล้ว?</span>
            <a href="{{ route('login') }}" class="small text-decoration-none fw-semibold ms-1">เข้าสู่ระบบที่นี่</a>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

