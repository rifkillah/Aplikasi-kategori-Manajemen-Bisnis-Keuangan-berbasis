<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $salesQuery = Transaction::sales()->completed()->betweenDates($from, $to);
        $revenue = (clone $salesQuery)->sum('total_amount');
        $transactionCount = (clone $salesQuery)->count();
        $itemsSold = TransactionDetail::whereHas('transaction', fn ($query) => $query->sales()->completed()->betweenDates($from, $to))->sum('quantity');
        $cogs = TransactionDetail::whereHas('transaction', fn ($query) => $query->sales()->completed()->betweenDates($from, $to))
            ->selectRaw('COALESCE(SUM(purchase_price * quantity), 0) as total')
            ->value('total');
        $expenses = Transaction::where('type', Transaction::TYPE_EXPENSE)->completed()->betweenDates($from, $to)->sum('total_amount');
        $otherIncome = Transaction::where('type', Transaction::TYPE_INCOME)->completed()->betweenDates($from, $to)->sum('total_amount');
        $grossProfit = (float) $revenue - (float) $cogs;
        $netProfit = $grossProfit + (float) $otherIncome - (float) $expenses;

        $topProducts = TransactionDetail::query()
            ->whereHas('transaction', fn ($query) => $query->sales()->completed()->betweenDates($from, $to))
            ->select(
                'product_name',
                'sku',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->groupBy('product_name', 'sku')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $daily = Transaction::query()
            ->where('status', 'completed')
            ->whereIn('type', [Transaction::TYPE_SALE, Transaction::TYPE_INCOME, Transaction::TYPE_EXPENSE])
            ->betweenDates($from, $to)
            ->selectRaw('DATE(transaction_date) as day, type, SUM(total_amount) as total')
            ->groupBy('day', 'type')
            ->orderBy('day')
            ->get()
            ->groupBy('day')
            ->map(function ($rows, $day) {
                return [
                    'day' => $day,
                    'income' => (float) $rows->whereIn('type', ['sale', 'income'])->sum('total'),
                    'expense' => (float) $rows->where('type', 'expense')->sum('total'),
                ];
            })
            ->values();

        return view('reports.index', compact(
            'from',
            'to',
            'revenue',
            'transactionCount',
            'itemsSold',
            'cogs',
            'expenses',
            'otherIncome',
            'grossProfit',
            'netProfit',
            'topProducts',
            'daily'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $transactions = Transaction::with(['customer', 'user'])
            ->completed()
            ->betweenDates($from, $to)
            ->orderBy('transaction_date')
            ->get();

        $filename = "laporan-transaksi-{$from}-{$to}.csv";

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Tanggal', 'Invoice', 'Tipe', 'Deskripsi', 'Pelanggan', 'Kasir',
                'Metode Bayar', 'Status Bayar', 'Subtotal', 'Diskon', 'Pajak', 'Total',
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->transaction_date->format('Y-m-d H:i'),
                    $transaction->invoice_number,
                    $transaction->type_label,
                    $transaction->description ?? ($transaction->details->first()->product_name ?? '-'),
                    $transaction->customer?->name ?? '-',
                    $transaction->user?->name ?? '-',
                    strtoupper($transaction->payment_method ?? '-'),
                    $transaction->payment_status_label,
                    (float) $transaction->subtotal,
                    (float) $transaction->discount,
                    (float) $transaction->tax,
                    (float) $transaction->total_amount,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(Request $request): array
    {
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
