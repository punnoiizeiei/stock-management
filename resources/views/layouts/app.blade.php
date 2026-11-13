<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบเบิก-รับหมึกพิมพ์') - บริษัท บิ๊กล็อต โฮมโปรดักส์ จำกัด</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

    <!-- Google Fonts: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --bs-font-sans-serif: 'Prompt', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --primary-color: #0d6efd;
            --secondary-bg: #f8fafc;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 0.85rem !important;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.12);
            color: #ffffff !important;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        }

        .stat-card {
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
        }

        .stat-card .icon-box {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.85rem;
            font-size: 1.5rem;
        }

        .badge-color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            vertical-align: middle;
            border: 1px solid rgba(0,0,0,0.15);
        }

        .btn {
            border-radius: 0.6rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .table-responsive {
            border-radius: 0.75rem;
        }

        .table th {
            font-weight: 600;
            color: #475569;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .pagination {
            margin-bottom: 0;
        }

        footer {
            margin-top: auto;
            background-color: #ffffff;
            border-top: 1px solid #e2e8f0;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container-fluid px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.jpg') }}" alt="โลโก้ บิ๊กล็อต โฮมโปรดักส์" class="rounded-3 shadow-sm" style="height: 42px; width: auto; object-fit: cover;">
                <div>
                    <div class="fw-bold fs-6 text-white lh-1">บิ๊กล็อต โฮมโปรดักส์</div>
                    <small class="text-white-50" style="font-size: 0.72rem;">บริษัท บิ๊กล็อต โฮมโปรดักส์ จำกัด &bull; ระบบเบิก-รับหมึก</small>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> แดชบอร์ด
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inks.*') ? 'active' : '' }}" href="{{ route('inks.index') }}">
                            <i class="bi bi-box-seam me-1"></i> คลังหมึกพิมพ์
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('transactions.stock_out*') ? 'active' : '' }}" href="{{ route('transactions.stock_out') }}">
                            <i class="bi bi-box-arrow-up-right text-warning me-1"></i> เบิกหมึก (เบิกได้ทันที)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('transactions.index') || request()->routeIs('transactions.show') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                            <i class="bi bi-clock-history me-1"></i> ประวัติการเบิก-รับ
                        </a>
                    </li>

                    {{-- Admin Only Settings --}}
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('transactions.stock_in*') ? 'active' : '' }}" href="{{ route('transactions.stock_in') }}">
                                <i class="bi bi-box-arrow-in-down text-info me-1"></i> รับเข้าสต็อก
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
                                <i class="bi bi-building me-1"></i> แผนก/ฝ่าย
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="bi bi-people-fill text-info me-1"></i> จัดการผู้ใช้
                            </a>
                        </li>
                    @endif
                </ul>

                <!-- Fast Actions & Auth Status -->
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('transactions.stock_out') }}" class="btn btn-warning text-dark btn-sm d-flex align-items-center gap-1 shadow-sm fw-semibold">
                        <i class="bi bi-box-arrow-up-right"></i> เบิกหมึก
                    </a>

                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('transactions.stock_in') }}" class="btn btn-success btn-sm d-flex align-items-center gap-1 shadow-sm fw-semibold">
                            <i class="bi bi-plus-circle"></i> รับหมึกเข้า
                        </a>

                        <!-- Admin Dropdown Menu -->
                        <div class="dropdown">
                            <button class="btn btn-dark border border-danger btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;">Admin</span>
                                <span class="text-white small fw-medium text-truncate" style="max-width: 140px;">
                                    {{ auth()->user()->name }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="border-radius: 0.75rem; min-width: 220px;">
                                <li class="px-3 py-2 border-bottom mb-1">
                                    <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                                    <small class="text-muted d-block">{{ auth()->user()->email }}</small>
                                    <span class="badge bg-danger-subtle text-danger border small mt-1">
                                        ผู้ดูแลระบบ (Admin)
                                    </span>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 small" href="{{ route('transactions.stock_in') }}">
                                        <i class="bi bi-box-arrow-in-down me-2 text-success"></i> รับหมึกเข้าสต็อก
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 small" href="{{ route('inks.create') }}">
                                        <i class="bi bi-plus-circle me-2 text-primary"></i> เพิ่มรุ่นหมึกใหม่
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 small" href="{{ route('departments.index') }}">
                                        <i class="bi bi-building me-2 text-primary"></i> จัดการแผนก
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 small" href="{{ route('users.index') }}">
                                        <i class="bi bi-people-fill me-2 text-primary"></i> จัดการผู้ใช้งาน
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item rounded-2 py-2 small text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ Admin
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <!-- Public / User view: Quick Admin Login Button -->
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-shield-lock"></i> เข้าสู่ระบบ Admin
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content Container -->
    <main class="container-fluid px-lg-4 py-4 flex-grow-1">

        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div class="flex-grow-1">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div class="flex-grow-1">
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-x-circle-fill fs-5"></i>
                    <strong>เกิดข้อผิดพลาดในการบันทึกข้อมูล:</strong>
                </div>
                <ul class="mb-0 ps-4">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Yield View Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center text-muted small">
        <div class="container-fluid px-lg-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div>
                <strong>บริษัท บิ๊กล็อต โฮมโปรดักส์ จำกัด</strong> &copy; {{ date('Y') }} &bull; ระบบจัดการสต็อกและเบิก-รับหมึกพิมพ์
            </div>
            <div>
                พัฒนาด้วย Laravel {{ app()->version() }} &bull; PHP {{ PHP_VERSION }} &bull; SQLite
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
