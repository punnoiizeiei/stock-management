<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['department'])->withCount('transactions');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('users.index', compact('users', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,user',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
        ]);

        return redirect()->route('users.index')
            ->with('success', "เพิ่มผู้ใช้งาน '{$validated['name']}' เรียบร้อยแล้ว");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,user',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        // Prevent self-demotion if the admin is editing their own account
        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'ไม่สามารถลดระดับสิทธิ์ของบัญชีที่คุณกำลังใช้งานอยู่ได้');
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', "อัปเดตข้อมูลผู้ใช้งาน '{$user->name}' สำเร็จแล้ว");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีผู้ใช้งานที่คุณกำลัง Login อยู่ได้');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "ลบผู้ใช้งาน '{$name}' เรียบร้อยแล้ว");
    }
}
