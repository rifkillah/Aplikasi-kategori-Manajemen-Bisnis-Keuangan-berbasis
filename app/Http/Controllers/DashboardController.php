<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $salesToday = Transaction::sales()->completed()
            ->whereDate('transaction_date', $today)
            ->sum('total_amount');

        $transactionsToday = Transaction::sales()->completed()
            ->whereDate('transaction_date', $today)
            ->count();

        $monthRevenue = Transaction::sales()->completed()
            ->whereBetween('transaction_date', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59'])
            ->sum('total_amount');

        $monthCogs = TransactionDetail::query()
            ->whereHas('transaction', function ($query) use ($monthStart, $monthEnd) {
                $query->sales()->completed()
                    ->whereBetween('transaction_date', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59']);
            })
            ->selectRaw('COALESCE(SUM(purchase_price * quantity), 0) as total')
            ->value('total');

        $monthExpenses = Transaction::where('type', Transaction::TYPE_EXPENSE)->completed()
            ->whereBetween('transaction_date', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59'])
            ->sum('total_amount');

        $monthIncome = Transaction::where('type', Transaction::TYPE_INCOME)->completed()
            ->whereBetween('transaction_date', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59'])
            ->sum('total_amount');

        $monthProfit = (float) $monthRevenue - (float) $monthCogs + (float) $monthIncome - (float) $monthExpenses;

        $lowStockProducts = Product::with('category')
            ->active()
            ->lowStock()
            ->orderBy('stock')
            ->limit(5)
            ->get();

        $lowStockCount = Product::active()->lowStock()->count();
        $totalProducts = Product::active()->count();

        $stockValue = Product::selectRaw('COALESCE(SUM(stock * purchase_price), 0) as total')->value('total');

        $recentTransactions = Transaction::with(['customer', 'user'])
            ->latest('transaction_date')
            ->limit(6)
            ->get();

        $topProducts = TransactionDetail::query()
            ->whereHas('transaction', function ($query) use ($monthStart, $monthEnd) {
                $query->sales()->completed()
                    ->whereBetween('transaction_date', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59']);
            })
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'salesToday',
            'transactionsToday',
            'monthRevenue',
            'monthProfit',
            'monthExpenses',
            'lowStockProducts',
            'lowStockCount',
            'totalProducts',
            'stockValue',
            'recentTransactions',
            'topProducts'
        ));
    }

    public function chartData(Request $request): JsonResponse
    {
        $months = collect(range(5, 0))->map(function ($offset) {
            $start = now()->copy()->subMonths($offset)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $income = Transaction::completed()
                ->whereIn('type', [Transaction::TYPE_SALE, Transaction::TYPE_INCOME])
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('total_amount');

            $expense = Transaction::completed()
                ->where('type', Transaction::TYPE_EXPENSE)
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('total_amount');

            return [
                'label' => $start->translatedFormat('M y'),
                'income' => (float) $income,
                'expense' => (float) $expense,
            ];
        });

        return response()->json([
            'labels' => $months->pluck('label'),
            'income' => $months->pluck('income'),
            'expense' => $months->pluck('expense'),
        ]);
    }
}
