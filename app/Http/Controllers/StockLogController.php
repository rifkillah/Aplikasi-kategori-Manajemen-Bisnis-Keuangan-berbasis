<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = StockLog::with(['product', 'user'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->whereHas('product', function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('product'), fn ($query) => $query->where('product_id', $request->integer('product')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')->get();
        $summary = [
            'in' => StockLog::where('qty', '>', 0)->whereDate('created_at', now()->toDateString())->sum('qty'),
            'out' => abs(StockLog::where('qty', '<', 0)->whereDate('created_at', now()->toDateString())->sum('qty')),
        ];

        return view('stock.index', compact('logs', 'products', 'summary'));
    }

    public function create(Request $request): View
    {
        $products = Product::active()->orderBy('name')->get();
        $selectedProduct = $request->filled('product') ? Product::find($request->integer('product')) : null;
        $type = in_array($request->string('type')->toString(), ['in', 'out', 'adjustment'], true)
            ? $request->string('type')->toString()
            : 'in';

        return view('stock.create', compact('products', 'selectedProduct', 'type'));
    }

    public function store(Request $request, StockService $stockService): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('tenant_id', $tenantId),
            ],
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => $request->input('type') === 'adjustment'
                ? ['required', 'integer', 'min:0']
                : ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $stockService->record(
            $product,
            $request->user(),
            $validated['type'],
            (int) $validated['quantity'],
            $validated['notes'] ?? null
        );

        return redirect()->route('products.show', $product)
            ->with('success', 'Pergerakan stok berhasil dicatat.');
    }
}
