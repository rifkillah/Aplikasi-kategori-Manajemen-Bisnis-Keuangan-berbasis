<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $products = Product::with('category')
            ->search($request->string('q')->toString())
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('status'), function ($query) use ($request) {
                match ($request->string('status')->toString()) {
                    'low' => $query->lowStock(),
                    'out' => $query->where('stock', '<=', 0),
                    'inactive' => $query->where('is_active', false),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        $summary = [
            'total' => Product::count(),
            'low' => Product::active()->lowStock()->count(),
            'stock_value' => Product::selectRaw('COALESCE(SUM(stock * purchase_price), 0) as total')->value('total'),
        ];

        return view('products.index', compact('products', 'categories', 'summary', 'tenantId'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $suggestedSku = 'PRD-'.strtoupper(substr(uniqid(), -6));

        return view('products.create', compact('categories', 'suggestedSku'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $this->normalizeBarcode($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required', 'string', 'max:64',
                Rule::unique('products', 'sku')->where(fn ($query) => $query->where('tenant_id', $tenantId)->whereNull('deleted_at')),
            ],
            'barcode' => [
                'nullable', 'string', 'max:64',
                Rule::unique('products', 'barcode')->where(fn ($query) => $query->where('tenant_id', $tenantId)->whereNull('deleted_at')),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('tenant_id', $tenantId),
            ],
            'unit' => ['required', 'string', 'max:24'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['is_active'] = $request->boolean('is_active');
        $initialStock = (int) $validated['stock'];

        $product = DB::transaction(function () use ($validated, $initialStock) {
            $product = Product::create($validated);

            if ($initialStock > 0) {
                StockLog::create([
                    'tenant_id' => $product->tenant_id,
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'qty' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'notes' => 'Stok awal produk',
                ]);
            }

            return $product;
        });

        return redirect()->route('products.show', $product)
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load('category');
        $stockLogs = $product->stockLogs()->with('user')->paginate(10);

        $sold = $product->transactionDetails()
            ->whereHas('transaction', fn ($query) => $query->sales()->completed())
            ->selectRaw('COALESCE(SUM(quantity), 0) as qty, COALESCE(SUM(subtotal), 0) as revenue')
            ->first();

        return view('products.show', compact('product', 'stockLogs', 'sold'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $this->normalizeBarcode($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required', 'string', 'max:64',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)->whereNull('deleted_at'))
                    ->ignore($product->id),
            ],
            'barcode' => [
                'nullable', 'string', 'max:64',
                Rule::unique('products', 'barcode')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)->whereNull('deleted_at'))
                    ->ignore($product->id),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('tenant_id', $tenantId),
            ],
            'unit' => ['required', 'string', 'max:24'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $product->update($validated);

        return redirect()->route('products.show', $product)
            ->with('success', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus. Riwayat transaksi tetap tersimpan.');
    }

    private function normalizeBarcode(Request $request): void
    {
        if (blank($request->input('barcode'))) {
            $request->merge(['barcode' => null]);
        }

        if (blank($request->input('category_id'))) {
            $request->merge(['category_id' => null]);
        }
    }
}
