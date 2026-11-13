<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::withCount('transactions')->orderBy('name')->paginate(10);

        return view('departments.index', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'code' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:100',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')
            ->with('success', 'เพิ่มแผนกเรียบร้อยแล้ว');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,'.$department->id,
            'code' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:100',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', 'แก้ไขข้อมูลแผนกเรียบร้อยแล้ว');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->transactions()->count() > 0) {
            return back()->with('error', 'ไม่สามารถลบแผนกนี้ได้ เนื่องจากมีประวัติการเบิกหมึกในระบบ');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'ลบแผนกเรียบร้อยแล้ว');
    }
}
