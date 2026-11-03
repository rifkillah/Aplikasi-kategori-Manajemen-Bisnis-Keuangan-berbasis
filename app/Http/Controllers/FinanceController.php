<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\InvoiceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $base = Transaction::query()
            ->whereIn('type', [Transaction::TYPE_INCOME, Transaction::TYPE_EXPENSE])
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->date('to')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where(function ($query) use ($term) {
                    $query->where('description', 'like', "%{$term}%")
                        ->orWhere('invoice_number', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%");
                });
            });

        $transactions = (clone $base)->with('user')->latest('transaction_date')->paginate(15)->withQueryString();

        $summary = [
            'income' => (clone $base)->where('type', Transaction::TYPE_INCOME)->sum('total_amount'),
            'expense' => (clone $base)->where('type', Transaction::TYPE_EXPENSE)->sum('total_amount'),
        ];
        $summary['difference'] = $summary['income'] - $summary['expense'];

        return view('finance.index', compact('transactions', 'summary'));
    }

    public function create(Request $request): View
    {
        $type = $request->string('type')->toString() === 'income' ? 'income' : 'expense';

        return view('finance.create', compact('type'));
    }

    public function store(Request $request, InvoiceNumberService $invoiceNumbers): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,transfer,qris,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = $request->user()->tenant;
        $date = Carbon::parse($validated['transaction_date']);
        $prefix = $validated['type'] === 'income' ? 'INC' : 'EXP';

        $transaction = Transaction::create([
            'tenant_id' => $tenant->id,
            'user_id' => $request->user()->id,
            'invoice_number' => $invoiceNumbers->next($tenant, $prefix, $date),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'subtotal' => $validated['amount'],
            'discount' => 0,
            'tax' => 0,
            'total_amount' => $validated['amount'],
            'paid_amount' => $validated['amount'],
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'status' => 'completed',
            'transaction_date' => $date->copy()->setTimeFrom(now()),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('finance.index')
            ->with('success', "Catatan {$transaction->type_label} berhasil disimpan.");
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        if ($transaction->type === Transaction::TYPE_SALE) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->route('finance.index')->with('success', 'Catatan keuangan berhasil dihapus.');
    }
}
