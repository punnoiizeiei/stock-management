<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ink;
use App\Models\InkTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        $totalInks = Ink::count();
        $totalTransactions = InkTransaction::count();
        $totalDepartments = Department::count();
        $totalUsers = User::count();

        return view('backup.index', compact('totalInks', 'totalTransactions', 'totalDepartments', 'totalUsers'));
    }

    /**
     * Download a complete JSON backup of the database
     */
    public function exportJson(): StreamedResponse
    {
        $fileName = 'backup_stock_biglot_'.now()->format('Y-m-d_His').'.json';

        $data = [
            'app' => 'Big Lot Home Products - Ink Stock Management',
            'exported_at' => now()->toIso8601String(),
            'version' => '1.0',
            'departments' => Department::all()->toArray(),
            'inks' => Ink::all()->toArray(),
            'transactions' => InkTransaction::all()->toArray(),
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Export Ink Catalog as CSV (compatible with Microsoft Excel)
     */
    public function exportInksCsv(): StreamedResponse
    {
        $fileName = 'inks_catalog_'.now()->format('Y-m-d').'.csv';

        $inks = Ink::orderBy('brand')->orderBy('model')->get();

        return response()->streamDownload(function () use ($inks) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Microsoft Excel Thai language compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'รหัสหมึก',
                'ยี่ห้อ (Brand)',
                'รุ่นหมึก (Model)',
                'ชื่อเต็ม',
                'สี (Color)',
                'ประเภท',
                'หน่วยนับ',
                'คงเหลือปัจจุบัน',
                'จุดเตือนขั้นต่ำ',
                'สถานที่จัดเก็บ',
                'เครื่องพิมพ์ที่รองรับ',
                'ราคาต่อหน่วย (บาท)',
                'หมายเหตุ',
            ]);

            foreach ($inks as $i) {
                fputcsv($handle, [
                    $i->code,
                    $i->brand,
                    $i->model,
                    $i->name,
                    $i->color,
                    $i->type,
                    $i->unit,
                    $i->current_stock,
                    $i->min_stock,
                    $i->location,
                    $i->compatible_printers,
                    $i->price_per_unit,
                    $i->notes,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export Transaction History as CSV (compatible with Microsoft Excel)
     */
    public function exportTransactionsCsv(): StreamedResponse
    {
        $fileName = 'transactions_history_'.now()->format('Y-m-d').'.csv';

        $transactions = InkTransaction::with('ink')->orderBy('action_date', 'desc')->orderBy('id', 'desc')->get();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Microsoft Excel Thai language compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'วันที่ทำรายการ',
                'เลขที่เอกสาร',
                'ประเภทรายการ',
                'รหัสหมึก',
                'ยี่ห้อ/รุ่น',
                'สี',
                'จำนวน',
                'หน่วยนับ',
                'สต็อกก่อนหน้า',
                'สต็อกหลังทำรายการ',
                'ชื่อผู้ทำรายการ',
                'แผนก/ฝ่าย',
                'เครื่องพิมพ์/จุดติดตั้ง',
                'เลขที่อ้างอิง/PO',
                'หมายเหตุ',
            ]);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->action_date ? Carbon::parse($tx->action_date)->format('Y-m-d') : '',
                    $tx->transaction_code,
                    $tx->type_label,
                    $tx->ink->code ?? '',
                    ($tx->ink->brand ?? '').' '.($tx->ink->model ?? ''),
                    $tx->ink->color ?? '',
                    $tx->quantity,
                    $tx->ink->unit ?? 'ตลับ',
                    $tx->stock_before,
                    $tx->stock_after,
                    $tx->person_name,
                    $tx->department_name,
                    $tx->printer_target,
                    $tx->reference_no,
                    $tx->notes,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Restore database from an uploaded JSON backup file
     */
    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json,txt|max:10240',
        ]);

        $content = file_get_contents($request->file('backup_file')->getRealPath());
        $data = json_decode($content, true);

        if (! is_array($data) || (! isset($data['inks']) && ! isset($data['departments']))) {
            return back()->with('error', 'ไฟล์ข้อมูลสำรองไม่ถูกต้อง กรุณาใช้ไฟล์ JSON ที่ดาวน์โหลดจากระบบนี้เท่านั้น');
        }

        try {
            DB::transaction(function () use ($data, $request) {
                // If clean restore requested, clear existing transactions & inks
                if ($request->boolean('clean_restore')) {
                    $driver = DB::getDriverName();
                    if ($driver === 'pgsql') {
                        DB::statement('TRUNCATE TABLE ink_transactions, inks RESTART IDENTITY CASCADE');
                    } else {
                        InkTransaction::query()->delete();
                        Ink::query()->delete();
                    }
                }

                // 1. Restore Departments
                if (! empty($data['departments']) && is_array($data['departments'])) {
                    foreach ($data['departments'] as $d) {
                        Department::updateOrCreate(
                            ['name' => $d['name']],
                            [
                                'code' => $d['code'] ?? null,
                                'contact_person' => $d['contact_person'] ?? null,
                            ]
                        );
                    }
                }

                // 2. Restore Inks
                $inkIdMap = [];
                if (! empty($data['inks']) && is_array($data['inks'])) {
                    foreach ($data['inks'] as $i) {
                        $oldId = $i['id'] ?? null;
                        unset($i['id'], $i['created_at'], $i['updated_at']);

                        $ink = Ink::updateOrCreate(
                            ['code' => $i['code']],
                            $i
                        );

                        if ($oldId) {
                            $inkIdMap[$oldId] = $ink->id;
                        }
                    }
                }

                // 3. Restore Transactions
                if (! empty($data['transactions']) && is_array($data['transactions'])) {
                    foreach ($data['transactions'] as $tx) {
                        $targetInkId = $inkIdMap[$tx['ink_id']] ?? $tx['ink_id'];
                        unset($tx['id'], $tx['created_at'], $tx['updated_at']);
                        $tx['ink_id'] = $targetInkId;

                        InkTransaction::updateOrCreate(
                            ['transaction_code' => $tx['transaction_code']],
                            $tx
                        );
                    }
                }
            });

            return redirect()->route('backup.index')->with('success', 'กู้คืนข้อมูลจากไฟล์สำรองสำเร็จเรียบร้อยแล้ว!');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการกู้คืนข้อมูล: '.$e->getMessage());
        }
    }
}
