<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login')
                ->with('error', 'ฟังก์ชันนี้สำหรับเจ้าหน้าที่ผู้ดูแลระบบ (Admin) เท่านั้น กรุณาเข้าสู่ระบบ');
        }

        if (! $request->user()->isAdmin()) {
            return redirect()->route('dashboard')
                ->with('error', 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้ (สงวนสิทธิ์เฉพาะผู้ดูแลระบบ Admin เท่านั้น)');
        }

        return $next($request);
    }
}
