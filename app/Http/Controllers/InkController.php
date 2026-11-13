<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InkController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ink::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('compatible_printers', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter Brand
        if ($brand = $request->input('brand')) {
            $query->where('brand', $brand);
        }

        // Filter Status
        if ($status = $request->input('status')) {
            if ($status === 'out') {
                $query->where('current_stock', '<=', 0);
            } elseif ($status === 'low') {
                $query->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0);
            } elseif ($status === 'normal') {
                $query->whereColumn('current_stock', '>', 'min_stock');
            }
        }

        $inks = $query->orderBy('brand')->orderBy('model')->paginate(12)->withQueryString();

        $brands = Ink::distinct()->orderBy('brand')->pluck('brand');
        $departments = Department::orderBy('name')->get();
        $allInks = Ink::orderBy('brand')->orderBy('model')->get();

        return view('inks.index', compact('inks', 'brands', 'departments', 'allInks'));
    }

    public function create(): View
    {
        $brands = ['HP', 'Canon', 'Epson', 'Brother', 'Ricoh', 'Fuji Xerox / Fujifilm', 'Samsung', 'Lexmark', 'Pantum'];
        $colors = [
            ['name' => 'Black (ดำ)', 'code' => '#1f2937'],
            ['name' => 'Cyan (ฟ้า)', 'code' => '#0284c7'],
            ['name' => 'Magenta (ชมพู/แดง)', 'code' => '#db2777'],
            ['name' => 'Yellow (เหลือง)', 'code' => '#eab308'],
            ['name' => 'Color / Tri-Color (รวม 3 สี)', 'code' => '#8b5cf6'],
            ['name' => 'Other / อื่นๆ', 'code' => '#6b7280'],
        ];
        $types = ['Toner Cartridge (เลเซอร์)', 'Ink Bottle (ขวดหมึกอิงค์แทงค์)', 'Ink Cartridge (ตลับหมึกอิงค์เจ็ท)', 'Ribbon (ผ้าหมึกพิมพ์ดอทเมทริกซ์)', 'Drum Unit (ชุดดรัม)'];
        $units = ['ตลับ', 'ขวด', 'กล่อง', 'ชิ้น', 'ม้วน'];

        return view('inks.create', compact('brands', 'colors', 'types', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:inks,code',
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'color_code' => 'required|string|max:20',
            'type' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'compatible_printers' => 'nullable|string',
            'location' => 'nullable|string|max:100',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $ink = Ink::create($validated);

        return redirect()->route('inks.show', $ink)
            ->with('success', "เพิ่มรายการหมึก '{$ink->name}' เรียบร้อยแล้ว");
    }

    public function show(Ink $ink): View
    {
        $transactions = $ink->transactions()->with('department')->paginate(10);
        $departments = Department::orderBy('name')->get();

        return view('inks.show', compact('ink', 'transactions', 'departments'));
    }

    public function edit(Ink $ink): View
    {
        $brands = ['HP', 'Canon', 'Epson', 'Brother', 'Ricoh', 'Fuji Xerox / Fujifilm', 'Samsung', 'Lexmark', 'Pantum'];
        $colors = [
            ['name' => 'Black (ดำ)', 'code' => '#1f2937'],
            ['name' => 'Cyan (ฟ้า)', 'code' => '#0284c7'],
            ['name' => 'Magenta (ชมพู/แดง)', 'code' => '#db2777'],
            ['name' => 'Yellow (เหลือง)', 'code' => '#eab308'],
            ['name' => 'Color / Tri-Color (รวม 3 สี)', 'code' => '#8b5cf6'],
            ['name' => 'Other / อื่นๆ', 'code' => '#6b7280'],
        ];
        $types = ['Toner Cartridge (เลเซอร์)', 'Ink Bottle (ขวดหมึกอิงค์แทงค์)', 'Ink Cartridge (ตลับหมึกอิงค์เจ็ท)', 'Ribbon (ผ้าหมึกพิมพ์ดอทเมทริกซ์)', 'Drum Unit (ชุดดรัม)'];
        $units = ['ตลับ', 'ขวด', 'กล่อง', 'ชิ้น', 'ม้วน'];

        return view('inks.edit', compact('ink', 'brands', 'colors', 'types', 'units'));
    }

    public function update(Request $request, Ink $ink): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:inks,code,'.$ink->id,
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'color_code' => 'required|string|max:20',
            'type' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'compatible_printers' => 'nullable|string',
            'location' => 'nullable|string|max:100',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $ink->update($validated);

        return redirect()->route('inks.show', $ink)
            ->with('success', "อัปเดตข้อมูลหมึก '{$ink->name}' สำเร็จแล้ว");
    }

    public function destroy(Ink $ink): RedirectResponse
    {
        $name = $ink->name;
        $ink->delete();

        return redirect()->route('inks.index')
            ->with('success', "ลบรายการหมึก '{$name}' เรียบร้อยแล้ว");
    }
}
