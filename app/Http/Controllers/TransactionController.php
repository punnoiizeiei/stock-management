<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ink;
use App\Models\InkTransaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = InkTransaction::with(['ink', 'department', 'user']);

        // Type filter
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Ink filter
        if ($inkId = $request->input('ink_id')) {
            $query->where('ink_id', $inkId);
        }

        // Department filter
        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        // Date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('action_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('action_date', '<=', $dateTo);
        }

        // Keyword search (code, person, reference, notes)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhere('person_name', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhere('printer_target', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('action_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $inks = Ink::orderBy('brand')->orderBy('model')->get();
        $departments = Department::orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'inks', 'departments'));
    }

    public function createStockIn(Request $request): View
    {
        $selectedInkId = $request->input('ink_id');
        $inks = Ink::orderBy('brand')->orderBy('model')->get();
        $departments = Department::orderBy('name')->get();

        return view('transactions.stock_in', compact('inks', 'departments', 'selectedInkId'));
    }

    public function storeStockIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ink_id' => 'required|exists:inks,id',
            'quantity' => 'required|integer|min:1',
            'person_name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'reference_no' => 'nullable|string|max:100',
            'action_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $userId = Auth::id();

        $transaction = DB::transaction(function () use ($validated, $userId) {
            $ink = Ink::lockForUpdate()->findOrFail($validated['ink_id']);
            $stockBefore = $ink->current_stock;
            $stockAfter = $stockBefore + $validated['quantity'];

            $dept = ! empty($validated['department_id']) ? Department::find($validated['department_id']) : null;

            // Generate transaction code
            $codePrefix = 'RC-'.Carbon::parse($validated['action_date'])->format('Ymd');
            $latestCount = InkTransaction::where('transaction_code', 'like', "{$codePrefix}-%")->count() + 1;
            $transactionCode = sprintf('%s-%04d', $codePrefix, $latestCount);

            $trx = InkTransaction::create([
                'transaction_code' => $transactionCode,
                'ink_id' => $ink->id,
                'user_id' => $userId,
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'person_name' => $validated['person_name'],
                'department_id' => $dept?->id,
                'department_name' => $dept?->name ?? 'ฝ่ายจัดซื้อ/คลัง',
                'reference_no' => $validated['reference_no'] ?? null,
                'printer_target' => null,
                'action_date' => $validated['action_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $ink->current_stock = $stockAfter;
            $ink->save();

            return $trx;
        });

        return redirect()->route('transactions.index')
            ->with('success', "บันทึกการรับหมึกเข้าสต็อกสำเร็จ (รหัสรายการ: {$transaction->transaction_code}, จำนวน: {$transaction->quantity} {$transaction->ink->unit})");
    }

    public function createStockOut(Request $request): View
    {
        $selectedInkId = $request->input('ink_id');
        $inks = Ink::orderBy('brand')->orderBy('model')->get();
        $departments = Department::orderBy('name')->get();

        return view('transactions.stock_out', compact('inks', 'departments', 'selectedInkId'));
    }

    public function storeStockOut(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ink_id' => 'required|exists:inks,id',
            'quantity' => 'required|integer|min:1',
            'person_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'printer_target' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:100',
            'action_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $userId = Auth::id();

        try {
            $transaction = DB::transaction(function () use ($validated, $userId) {
                $ink = Ink::lockForUpdate()->findOrFail($validated['ink_id']);

                if ($ink->current_stock < $validated['quantity']) {
                    throw new \Exception("สต็อกหมึก '{$ink->name}' ไม่เพียงพอสำหรับการเบิก! ปัจจุบันคงเหลือ {$ink->current_stock} {$ink->unit} (ต้องการเบิก {$validated['quantity']} {$ink->unit})");
                }

                $stockBefore = $ink->current_stock;
                $stockAfter = $stockBefore - $validated['quantity'];

                $dept = Department::findOrFail($validated['department_id']);

                $codePrefix = 'REQ-'.Carbon::parse($validated['action_date'])->format('Ymd');
                $latestCount = InkTransaction::where('transaction_code', 'like', "{$codePrefix}-%")->count() + 1;
                $transactionCode = sprintf('%s-%04d', $codePrefix, $latestCount);

                $trx = InkTransaction::create([
                    'transaction_code' => $transactionCode,
                    'ink_id' => $ink->id,
                    'user_id' => $userId,
                    'type' => 'out',
                    'quantity' => $validated['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'person_name' => $validated['person_name'],
                    'department_id' => $dept->id,
                    'department_name' => $dept->name,
                    'reference_no' => $validated['reference_no'] ?? null,
                    'printer_target' => $validated['printer_target'] ?? null,
                    'action_date' => $validated['action_date'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                $ink->current_stock = $stockAfter;
                $ink->save();

                return $trx;
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('transactions.index')
            ->with('success', "บันทึกการเบิกหมึกสำเร็จ (รหัสใบเบิก: {$transaction->transaction_code}, จำนวน: {$transaction->quantity} {$transaction->ink->unit})");
    }

    public function show(InkTransaction $transaction): View
    {
        $transaction->load(['ink', 'department', 'user']);

        return view('transactions.slip', compact('transaction'));
    }

    public function resetHistory(Request $request): RedirectResponse
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('TRUNCATE TABLE ink_transactions RESTART IDENTITY CASCADE');
        } elseif ($driver === 'sqlite') {
            DB::table('ink_transactions')->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'ink_transactions'");
        } else {
            DB::table('ink_transactions')->truncate();
        }

        if ($request->boolean('reset_ink_stock')) {
            Ink::query()->update(['current_stock' => 0]);
        }

        return redirect()->route('transactions.index')->with('success', 'ล้างประวัติการเบิก-รับหมึกพิมพ์ทั้งหมดเรียบร้อยแล้ว เลขที่เอกสารถูกรีเซ็ตกลับไปเริ่มต้นที่ 1');
    }

    public function destroy(InkTransaction $transaction): RedirectResponse
    {
        $code = $transaction->transaction_code;
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', "ลบรายการ {$code} เรียบร้อยแล้ว");
    }

    public function apiInkInfo(Ink $ink): JsonResponse
    {
        return response()->json([
            'id' => $ink->id,
            'code' => $ink->code,
            'name' => $ink->name,
            'brand' => $ink->brand,
            'model' => $ink->model,
            'color' => $ink->color,
            'color_code' => $ink->color_code,
            'unit' => $ink->unit,
            'current_stock' => $ink->current_stock,
            'min_stock' => $ink->min_stock,
            'location' => $ink->location,
            'compatible_printers' => $ink->compatible_printers,
            'stock_status' => $ink->stock_status,
            'status_label' => $ink->status_label,
        ]);
    }
}
