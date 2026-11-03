<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\SaleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::with(['customer', 'user'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where(function ($query) use ($term) {
                    $query->where('invoice_number', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('payment_status', $request->string('status')->toString()))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->date('to')))
            ->latest('transaction_date')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'revenue' => Transaction::sales()->completed()
                ->when($request->filled('from'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->date('from')))
                ->when($request->filled('to'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->date('to')))
                ->sum('total_amount'),
            'count' => Transaction::sales()->completed()
                ->when($request->filled('from'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->date('from')))
                ->when($request->filled('to'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->date('to')))
                ->count(),
        ];

        return view('transactions.index', compact('transactions', 'summary'));
    }

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load(['details.product', 'customer', 'user', 'tenant']);

        return view('transactions.show', compact('transaction'));
    }

    public function pdf(Transaction $transaction): Response
    {
        $this->authorize('view', $transaction);

        $transaction->load(['details', 'customer', 'user', 'tenant']);

        $logoPath = null;
        if ($transaction->tenant?->logo) {
            $candidate = Storage::disk('public')->path($transaction->tenant->logo);
            $logoPath = file_exists($candidate) ? $candidate : null;
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'transaction' => $transaction,
            'tenant' => $transaction->tenant,
            'logoPath' => $logoPath,
        ])->setPaper('a4');

        $filename = 'Invoice-'.str_replace('/', '-', $transaction->invoice_number).'.pdf';

        return $pdf->download($filename);
    }

    public function void(Transaction $transaction, SaleService $saleService): RedirectResponse
    {
        $this->authorize('void', $transaction);

        $saleService->void($transaction, auth()->user());

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil dibatalkan dan stok telah dikembalikan.');
    }
}
