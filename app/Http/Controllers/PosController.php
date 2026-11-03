<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => (float) $product->selling_price,
                'stock' => $product->stock,
                'unit' => $product->unit,
                'category' => $product->category?->name ?? 'Tanpa kategori',
            ]);

        $customers = Customer::orderBy('name')->get();
        $tenant = auth()->user()->tenant;

        return view('pos.index', compact('products', 'customers', 'tenant'));
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        $product = Product::with('category')
            ->active()
            ->where(function ($query) use ($validated) {
                $query->where('barcode', $validated['code'])
                    ->orWhere('sku', $validated['code']);
            })
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Produk dengan barcode/SKU tersebut tidak ditemukan.'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'price' => (float) $product->selling_price,
            'stock' => $product->stock,
            'unit' => $product->unit,
            'category' => $product->category?->name ?? 'Tanpa kategori',
        ]);
    }

    public function store(Request $request, SaleService $saleService): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required', 'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->whereNull('deleted_at')),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where('tenant_id', $tenantId),
            ],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,transfer,qris,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'items.required' => 'Tambahkan minimal satu produk ke keranjang.',
            'items.min' => 'Tambahkan minimal satu produk ke keranjang.',
        ]);

        if (blank($validated['customer_id'] ?? null)) {
            $validated['customer_id'] = null;
        }

        $transaction = $saleService->create($validated, $request->user());

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Transaksi {$transaction->invoice_number} berhasil disimpan.");
    }
}
