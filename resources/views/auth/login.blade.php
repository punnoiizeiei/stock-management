<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบจัดการสต็อกและเบิก-รับหมึกพิมพ์</title>

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
            max-width: 440px;
        }

        .form-control {
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
        <!-- Logo & Header -->
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo.jpg') }}" alt="โลโก้ บริษัท บิ๊กล็อต โฮมโปรดักส์ จำกัด" class="rounded-4 shadow-sm mb-3" style="height: 76px; width: auto; object-fit: cover;">
            <h5 class="fw-bold mb-1 text-dark">บริษัท บิ๊กล็อต โฮมโปรดักส์ จำกัด</h5>
            <p class="text-muted small mb-0">ระบบจัดการสต็อกและเบิก-รับหมึกพิมพ์ (ผู้ดูแลระบบ)</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show small rounded-3" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show small rounded-3" role="alert">
                <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show small rounded-3" role="alert">
                <i class="bi bi-x-circle me-1"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('login.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">อีเมล (Email)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" id="emailInput" value="{{ old('email') }}" class="form-control border-start-0" placeholder="admin@stock.com" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-muted">รหัสผ่าน (Password)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control border-start-0" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                    <label class="form-check-label small text-muted" for="remember">
                        จดจำการเข้าสู่ระบบ
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3 shadow-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
            </button>
        </form>

        <div class="text-center mt-3 pt-3 border-top">
            <a href="{{ route('dashboard') }}" class="small text-decoration-none text-muted">
                <i class="bi bi-arrow-left me-1"></i> กลับสู่หน้าหลัก (ไปเบิกหมึก)
            </a>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
