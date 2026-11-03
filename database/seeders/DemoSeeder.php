<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'kopi-senja'],
            [
                'business_name' => 'Kopi Senja Nusantara',
                'address' => 'Jl. Cihampelas No. 88, Bandung, Jawa Barat',
                'phone' => '0812-3456-7890',
                'email' => 'halo@kopisenja.id',
                'invoice_prefix' => 'KSN',
                'currency' => 'IDR',
                'tax_percent' => 11,
            ]
        );

        $users = [
            ['name' => 'Aditya Pratama', 'email' => 'owner@demo.com', 'role' => 'owner'],
            ['name' => 'Sari Wulandari', 'email' => 'kasir@demo.com', 'role' => 'kasir'],
            ['name' => 'Budi Santoso', 'email' => 'gudang@demo.com', 'role' => 'gudang'],
        ];

        $createdUsers = [];
        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$data['role']]);
            $createdUsers[$data['role']] = $user;
        }

        $categories = [
            'Kopi' => 'Espresso based & manual brew',
            'Non-Kopi' => 'Teh, cokelat, dan minuman segar',
            'Makanan Ringan' => 'Pastry dan camilan pendamping',
            'Bahan Baku' => 'Biji kopi dan bahan produksi',
            'Merchandise' => 'Tumbler, tote bag, dan lainnya',
        ];

        $categoryModels = collect($categories)->mapWithKeys(function ($description, $name) use ($tenant) {
            $category = Category::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $name],
                ['description' => $description]
            );

            return [$name => $category];
        });

        $productData = [
            ['Kopi Susu Senja', 'KSN-KOPI-01', '8991001000011', 'Kopi', 'pcs', 12000, 24000, 86, 15],
            ['Americano', 'KSN-KOPI-02', '8991001000028', 'Kopi', 'pcs', 9000, 20000, 74, 15],
            ['Cappuccino', 'KSN-KOPI-03', '8991001000035', 'Kopi', 'pcs', 14000, 28000, 48, 12],
            ['Caramel Latte', 'KSN-KOPI-04', '8991001000042', 'Kopi', 'pcs', 15000, 30000, 9, 12],
            ['Cold Brew 250ml', 'KSN-KOPI-05', '8991001000059', 'Kopi', 'botol', 13000, 27000, 32, 10],
            ['Matcha Latte', 'KSN-NK-01', '8991001000066', 'Non-Kopi', 'pcs', 16000, 30000, 27, 10],
            ['Chocolate Ice', 'KSN-NK-02', '8991001000073', 'Non-Kopi', 'pcs', 13000, 26000, 44, 10],
            ['Teh Lemon Madu', 'KSN-NK-03', '8991001000080', 'Non-Kopi', 'pcs', 8000, 18000, 6, 12],
            ['Croissant Butter', 'KSN-FNB-01', '8991001000097', 'Makanan Ringan', 'pcs', 9000, 20000, 18, 8],
            ['Cinnamon Roll', 'KSN-FNB-02', '8991001000103', 'Makanan Ringan', 'pcs', 10000, 22000, 4, 8],
            ['Banana Bread', 'KSN-FNB-03', '8991001000110', 'Makanan Ringan', 'pcs', 8500, 18000, 25, 8],
            ['Biji House Blend 250gr', 'KSN-BHN-01', '8991001000127', 'Bahan Baku', 'pack', 45000, 85000, 21, 5],
            ['Biji Arabika Gayo 250gr', 'KSN-BHN-02', '8991001000134', 'Bahan Baku', 'pack', 58000, 105000, 3, 5],
            ['Tumbler Senja 500ml', 'KSN-MER-01', '8991001000141', 'Merchandise', 'pcs', 55000, 115000, 12, 4],
        ];

        $products = collect();
        foreach ($productData as [$name, $sku, $barcode, $category, $unit, $buy, $sell, $stock, $min]) {
            $products->push(Product::updateOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $sku],
                [
                    'category_id' => $categoryModels[$category]->id,
                    'barcode' => $barcode,
                    'name' => $name,
                    'unit' => $unit,
                    'purchase_price' => $buy,
                    'selling_price' => $sell,
                    'stock' => $stock,
                    'min_stock_alert' => $min,
                    'is_active' => true,
                ]
            ));
        }

        $customerData = [
            ['Rina Kartika', '0812-9000-1122', 'rina.kartika@mail.com', 'Jl. Setiabudi No. 12, Bandung'],
            ['Dimas Saputra', '0813-8111-2233', 'dimas.s@mail.com', 'Jl. Dago No. 45, Bandung'],
            ['Nadia Putri', '0857-9222-3344', 'nadia.putri@mail.com', 'Komplek Gegerkalong Blok C2'],
            ['Raka Mahendra', '0821-1333-4455', 'raka.m@mail.com', 'Jl. Riau No. 8, Bandung'],
            ['CV Rasa Nusantara', '022-777-8899', 'finance@rasanusantara.id', 'Jl. Soekarno Hatta No. 210, Bandung'],
            ['Maya Lestari', '0819-2444-5566', 'maya.l@mail.com', 'Jl. Pasteur No. 33, Bandung'],
            ['Fajar Nugroho', '0838-3555-6677', 'fajar.n@mail.com', 'Jl. Ciumbuleuit No. 91, Bandung'],
            ['Kantor Kreatif Ruang', '022-555-1234', 'hello@ruangkreatif.id', 'Jl. Braga No. 56, Bandung'],
        ];

        $customers = collect($customerData)->map(fn ($data) => Customer::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $data[0]],
            ['phone' => $data[1], 'email' => $data[2], 'address' => $data[3]]
        ));

        // Idempotent demo data
        Transaction::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
        StockLog::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();

        $owner = $createdUsers['owner'];
        $kasir = $createdUsers['kasir'];
        $gudang = $createdUsers['gudang'];

        foreach ($products as $product) {
            $initial = max(150, $product->stock + 50);
            StockLog::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'user_id' => $gudang->id,
                'type' => 'in',
                'qty' => $initial,
                'stock_before' => 0,
                'stock_after' => $initial,
                'notes' => 'Stok awal pembukaan gerai',
                'created_at' => now()->subMonths(6)->startOfMonth()->addDays(2),
                'updated_at' => now()->subMonths(6)->startOfMonth()->addDays(2),
            ]);

            StockLog::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'user_id' => $gudang->id,
                'type' => 'adjustment',
                'qty' => $product->stock - $initial,
                'stock_before' => $initial,
                'stock_after' => $product->stock,
                'notes' => 'Penyesuaian stok opname bulanan',
                'created_at' => now()->subDays(rand(2, 10)),
                'updated_at' => now()->subDays(rand(2, 10)),
            ]);
        }

        $paymentMethods = ['cash', 'cash', 'cash', 'qris', 'qris', 'transfer'];
        $dailySequence = [];
        $salesCreated = 0;

        foreach (range(5, 0) as $monthOffset) {
            $month = now()->copy()->subMonths($monthOffset);
            $daysInMonth = $month->daysInMonth;
            $maxDay = $monthOffset === 0 ? min($daysInMonth, now()->day) : $daysInMonth;

            for ($i = 0; $i < 18; $i++) {
                $date = $month->copy()->startOfMonth()->addDays(rand(0, max(0, $maxDay - 1)))->setTime(rand(8, 20), rand(0, 59));
                $dayKey = $date->format('Ymd');
                $dailySequence[$dayKey] = ($dailySequence[$dayKey] ?? 0) + 1;

                $items = $products->random(rand(1, 3));
                $subtotal = 0;
                $lines = [];

                foreach ($items as $product) {
                    $qty = rand(1, 3);
                    $lineSubtotal = (float) $product->selling_price * $qty;
                    $subtotal += $lineSubtotal;
                    $lines[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'quantity' => $qty,
                        'price' => $product->selling_price,
                        'purchase_price' => $product->purchase_price,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                $discount = rand(0, 4) === 0 ? 5000 : 0;
                $tax = round(($subtotal - $discount) * 0.11, 2);
                $total = $subtotal - $discount + $tax;
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $paymentRoll = rand(1, 100);
                $paymentStatus = $paymentRoll <= 82 ? 'paid' : ($paymentRoll <= 93 ? 'partial' : 'unpaid');
                $paid = match ($paymentStatus) {
                    'paid' => $total,
                    'partial' => round($total * 0.5),
                    default => 0,
                };

                $transaction = Transaction::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $kasir->id,
                    'customer_id' => rand(0, 3) === 0 ? null : $customers->random()->id,
                    'invoice_number' => sprintf('KSN-%s-%04d', $dayKey, $dailySequence[$dayKey]),
                    'type' => 'sale',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                    'transaction_date' => $date,
                    'notes' => rand(0, 5) === 0 ? 'Dine in — meja '.rand(1, 8) : null,
                ]);

                $transaction->details()->createMany($lines);
                $salesCreated++;
            }

            $expenseDate = $month->copy()->startOfMonth()->addDays(4)->setTime(10, 0);
            $expenses = [
                ['Sewa tempat & service charge', 4500000],
                ['Gaji barista dan staf', 7200000],
                ['Listrik, air & internet', 1250000],
            ];
            foreach ($expenses as $index => [$description, $amount]) {
                Transaction::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $owner->id,
                    'invoice_number' => sprintf('EXP-%s-%04d', $expenseDate->format('Ymd'), $index + 1),
                    'type' => 'expense',
                    'description' => $description,
                    'subtotal' => $amount,
                    'total_amount' => $amount,
                    'paid_amount' => $amount,
                    'payment_status' => 'paid',
                    'payment_method' => 'transfer',
                    'status' => 'completed',
                    'transaction_date' => $expenseDate->copy()->addDays($index),
                ]);
            }

            if ($monthOffset % 2 === 0) {
                $incomeAmount = rand(8, 18) * 500000;

                Transaction::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $owner->id,
                    'invoice_number' => sprintf('INC-%s-0001', $month->copy()->startOfMonth()->addDays(12)->format('Ymd')),
                    'type' => 'income',
                    'description' => 'Pesanan katering kantor',
                    'subtotal' => $incomeAmount,
                    'total_amount' => $incomeAmount,
                    'paid_amount' => $incomeAmount,
                    'payment_status' => 'paid',
                    'payment_method' => 'transfer',
                    'status' => 'completed',
                    'transaction_date' => $month->copy()->startOfMonth()->addDays(12)->setTime(14, 0),
                ]);
            }
        }

        $lowStockProduct = $products->first(fn (Product $product) => $product->is_low_stock);
        if ($lowStockProduct) {
            $owner->notify(new LowStockNotification($lowStockProduct));
        }

        // Keep a reference to the demo logo path if a previous asset exists.
        if (file_exists(public_path('images/demo-logo.png')) && ! $tenant->logo) {
            Storage::disk('public')->put('tenant-logos/demo-logo.png', file_get_contents(public_path('images/demo-logo.png')));
            $tenant->update(['logo' => 'tenant-logos/demo-logo.png']);
        }

        $this->command?->info("Demo data siap: {$salesCreated} transaksi penjualan, {$products->count()} produk, {$customers->count()} pelanggan.");
        $this->command?->info('Login: owner@demo.com / kasir@demo.com / gudang@demo.com — password: password');
    }
}
