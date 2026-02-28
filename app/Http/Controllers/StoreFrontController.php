<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\FlashSale;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreFrontController extends Controller
{
    public function home(Request $request)
    {
        $setting = StoreSetting::current();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $query = Product::where('is_active', true)->with(['category', 'variants' => fn($q) => $q->where('is_active', true)]);

        if ($request->filled('kategori')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->kategori));
        }
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $products = $query->orderBy('sort_order')->paginate(16);

        $flashSales = FlashSale::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->with('product')
            ->get();

        // Top customers berdasarkan jumlah order yang SELESAI saja
        $topCustomers = Order::select('customer_name', 'customer_phone')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(total) as total_spent')
            ->where('status', 'completed')
            ->groupBy('customer_name', 'customer_phone')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get();

        return view('home', compact('products', 'categories', 'flashSales', 'setting', 'topCustomers'));
    }

    public function productDetail(string $slug)
    {
        $setting = StoreSetting::current();
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'variants' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->firstOrFail();

        $flashSale = $product->activeFlashSale();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        // Ambil testimoni yang sudah di-approve untuk produk ini
        $testimonials = Testimonial::approved()
            ->where('product_name', $product->name)
            ->latest()
            ->limit(20)
            ->get();

        return view('product-detail', compact('product', 'flashSale', 'setting', 'paymentMethods', 'testimonials'));
    }

    public function flashSale()
    {
        $setting = StoreSetting::current();
        $flashSales = FlashSale::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->with(['product', 'variant'])
            ->get();

        return view('flash-sale', compact('flashSales', 'setting'));
    }

    public function cart()
    {
        $setting = StoreSetting::current();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();
        return view('cart', compact('setting', 'paymentMethods'));
    }

    public function articles(Request $request)
    {
        $setting = StoreSetting::current();
        $articles = Article::published()
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('articles', compact('articles', 'setting'));
    }

    public function articleDetail(string $slug)
    {
        $setting = StoreSetting::current();
        $article = Article::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('article-detail', compact('article', 'relatedArticles', 'setting'));
    }

    public function invoice(Request $request)
    {
        $setting = StoreSetting::current();
        $orders = collect();
        $searched = false;

        if ($request->filled('phone')) {
            $searched = true;
            $query = Order::where('customer_phone', $request->phone)
                ->with(['items', 'paymentMethod']);

            if ($request->filled('from')) {
                $query->whereDate('ordered_at', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('ordered_at', '<=', $request->to);
            }

            $orders = $query->orderBy('ordered_at', 'desc')->get();
        }

        return view('invoice', compact('setting', 'orders', 'searched'));
    }

    public function searchProducts(Request $request)
    {
        $products = Product::where('is_active', true)
            ->where('name', 'like', '%' . $request->q . '%')
            ->with('category')
            ->limit(8)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'image' => $p->image ? asset('storage/' . $p->image) : null,
                'base_price' => $p->base_price,
                'badge' => $p->badge,
            ]);

        return response()->json($products);
    }

    public function getSettings()
    {
        $s = StoreSetting::current();
        return response()->json([
            'whatsapp_number' => $s->whatsapp_number,
            'message_template' => $s->message_template,
            'store_name' => $s->store_name,
        ]);
    }

    public function validateDiscount(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $code = DiscountCode::where('code', strtoupper($request->code))->first();

        if (!$code) {
            return response()->json(['valid' => false, 'message' => 'Kode diskon tidak ditemukan']);
        }

        if (!$code->isValid((float) $request->amount)) {
            if (!$code->is_active) {
                return response()->json(['valid' => false, 'message' => 'Kode diskon tidak aktif']);
            }
            if ($code->expired_at && $code->expired_at->isPast()) {
                return response()->json(['valid' => false, 'message' => 'Kode diskon sudah kadaluarsa']);
            }
            if ($code->max_uses !== null && $code->used_count >= $code->max_uses) {
                return response()->json(['valid' => false, 'message' => 'Kode diskon sudah habis penggunaannya']);
            }
            if ((float) $request->amount < $code->min_purchase) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Minimal pembelian Rp ' . number_format($code->min_purchase, 0, ',', '.'),
                ]);
            }
            return response()->json(['valid' => false, 'message' => 'Kode diskon tidak valid']);
        }

        $discount = $code->calculateDiscount((float) $request->amount);

        return response()->json([
            'valid' => true,
            'discount_code_id' => $code->id,
            'discount' => $discount,
            'description' => $code->description,
            'message' => 'Diskon berhasil diterapkan',
        ]);
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = DB::transaction(function () use ($request) {
                $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
                $setting = StoreSetting::current();

                // Build items
                $subtotal = 0;
                $orderItems = [];

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $variant = null;
                    $price = (float) $product->base_price;

                    if (!empty($item['variant_id'])) {
                        $variant = ProductVariant::find($item['variant_id']);
                        if ($variant) {
                            $price = (float) $variant->price;
                        }
                    }

                    // Check flash sale
                    $flashSale = $product->activeFlashSale();
                    if ($flashSale && !$variant) {
                        $price = (float) $flashSale->flash_price;
                    }

                    $qty = (int) $item['quantity'];
                    $lineSubtotal = $price * $qty;
                    $subtotal += $lineSubtotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'product_name' => $product->name,
                        'variant_name' => $variant?->name,
                        'price' => $price,
                        'quantity' => $qty,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                // Admin fee
                $adminFee = $paymentMethod->calculateFee($subtotal);

                // Discount
                $discountAmount = 0;
                $discountCodeId = null;
                if ($request->filled('discount_code')) {
                    $discountCode = DiscountCode::where('code', strtoupper($request->discount_code))->first();
                    if ($discountCode && $discountCode->isValid($subtotal)) {
                        $discountAmount = $discountCode->calculateDiscount($subtotal);
                        $discountCodeId = $discountCode->id;
                        $discountCode->increment('used_count');
                    }
                }

                // Unique code (1–999)
                $uniqueCode = rand(1, 999);

                // Total
                $total = $subtotal + $adminFee - $discountAmount + $uniqueCode;

                // Create order
                $order = Order::create([
                    'order_code' => Order::generateOrderCode(),
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_note' => $request->customer_note,
                    'payment_method_id' => $paymentMethod->id,
                    'discount_code_id' => $discountCodeId,
                    'subtotal' => $subtotal,
                    'admin_fee' => $adminFee,
                    'discount_amount' => $discountAmount,
                    'unique_code' => $uniqueCode,
                    'total' => $total,
                    'status' => 'pending',
                    'ordered_at' => now(),
                ]);

                // Create order items
                foreach ($orderItems as $oi) {
                    $order->items()->create($oi);
                }

                // Build WhatsApp message
                $itemsText = collect($orderItems)->map(function ($oi) {
                    $line = "• {$oi['product_name']}";
                    if ($oi['variant_name'])
                        $line .= " ({$oi['variant_name']})";
                    $line .= " x{$oi['quantity']} = Rp " . number_format($oi['subtotal'], 0, ',', '.');
                    return $line;
                })->implode("\n");

                $template = $setting->message_template;
                $message = str_replace(
                    ['{items}', '{subtotal}', '{admin_fee}', '{discount}', '{unique_code}', '{total}', '{payment}', '{name}', '{phone}', '{note}', '{order_code}'],
                    [
                        $itemsText,
                        'Rp ' . number_format($subtotal, 0, ',', '.'),
                        'Rp ' . number_format($adminFee, 0, ',', '.'),
                        'Rp ' . number_format($discountAmount, 0, ',', '.'),
                        $uniqueCode,
                        'Rp ' . number_format($total, 0, ',', '.'),
                        $paymentMethod->name,
                        $request->customer_name,
                        $request->customer_phone,
                        $request->customer_note ?? '-',
                        $order->order_code,
                    ],
                    $template
                );

                $waNumber = preg_replace('/[^0-9]/', '', $setting->whatsapp_number);
                $whatsappLink = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($message);

                return [
                    'order' => $order,
                    'whatsapp_link' => $whatsappLink,
                ];
            });

            return response()->json([
                'success' => true,
                'order_code' => $order['order']->order_code,
                'whatsapp_link' => $order['whatsapp_link'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function validateOrderCode(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string',
        ]);

        $order = Order::where('order_code', strtoupper($request->order_code))
            ->with('items')
            ->first();

        if (!$order) {
            return response()->json(['valid' => false, 'message' => 'Kode pembelian tidak ditemukan']);
        }

        // Cek apakah sudah pernah testimonial dengan kode ini
        $exists = Testimonial::where('order_code', $order->order_code)->exists();
        if ($exists) {
            return response()->json(['valid' => false, 'message' => 'Kode pembelian ini sudah pernah digunakan untuk testimoni']);
        }

        // Ambil nama produk dari order items
        $productNames = $order->items->pluck('product_name')->implode(', ');

        return response()->json([
            'valid' => true,
            'customer_name' => $order->customer_name,
            'product_name' => $productNames,
            'order_code' => $order->order_code,
        ]);
    }

    public function submitTestimonial(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:500',
        ]);

        $order = Order::where('order_code', strtoupper($request->order_code))
            ->with('items')
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Kode pembelian tidak valid']);
        }

        $exists = Testimonial::where('order_code', $order->order_code)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Anda sudah pernah memberi testimoni']);
        }

        $productNames = $order->items->pluck('product_name')->implode(', ');

        Testimonial::create([
            'order_code' => $order->order_code,
            'customer_name' => $order->customer_name,
            'rating' => $request->rating,
            'content' => $request->content,
            'product_name' => $productNames,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih! Testimoni Anda akan ditampilkan setelah disetujui admin.',
        ]);
    }
}
