<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ink;
use App\Models\InkTransaction;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalInks = Ink::count();
        $totalStockUnits = Ink::sum('current_stock');

        $lowStockCount = Ink::whereColumn('current_stock', '<=', 'min_stock')
            ->where('current_stock', '>', 0)
            ->count();

        $outOfStockCount = Ink::where('current_stock', '<=', 0)->count();

        // Current Month stats
        $startOfMonth = Carbon::now()->startOfMonth();
        $monthlyStockIn = InkTransaction::where('type', 'in')
            ->where('action_date', '>=', $startOfMonth)
            ->sum('quantity');

        $monthlyStockOut = InkTransaction::where('type', 'out')
            ->where('action_date', '>=', $startOfMonth)
            ->sum('quantity');

        // Low stock items list (limit 6)
        $alertInks = Ink::where(function ($query) {
            $query->whereColumn('current_stock', '<=', 'min_stock')
                ->orWhere('current_stock', '<=', 0);
        })
            ->orderBy('current_stock', 'asc')
            ->take(6)
            ->get();

        // Recent 10 transactions
        $recentTransactions = InkTransaction::with(['ink', 'department'])
            ->orderBy('action_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        // Chart 1: Stock distribution by brand
        $brandDistribution = Ink::selectRaw('brand, SUM(current_stock) as total')
            ->groupBy('brand')
            ->orderBy('total', 'desc')
            ->pluck('total', 'brand');

        // Chart 2: Top requisitions by Department
        $deptRequisitions = InkTransaction::where('type', 'out')
            ->selectRaw('COALESCE(department_name, "ไม่ระบุ") as dept, SUM(quantity) as total')
            ->groupBy('dept')
            ->orderBy('total', 'desc')
            ->take(6)
            ->pluck('total', 'dept');

        $inks = Ink::orderBy('brand')->orderBy('model')->get();
        $departments = Department::orderBy('name')->get();

        return view('dashboard', compact(
            'totalInks',
            'totalStockUnits',
            'lowStockCount',
            'outOfStockCount',
            'monthlyStockIn',
            'monthlyStockOut',
            'alertInks',
            'recentTransactions',
            'brandDistribution',
            'deptRequisitions',
            'inks',
            'departments'
        ));
    }
}
