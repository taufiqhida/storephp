<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Models\Testimonial;
use App\Models\FlashSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreFrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::create([
            'store_name' => 'Test Store',
            'whatsapp_number' => '6281234567890',
            'site_mode' => 'live',
            'message_template' => 'Test {items} {total} {payment} {name} {phone} {note} {order_code}',
        ]);
    }

    // ═══════════════════════════════════
    // HOMEPAGE
    // ═══════════════════════════════════

    public function test_homepage_returns_200(): void
    {
        $category = Category::create(['name' => 'Test Cat', 'slug' => 'test-cat', 'is_active' => true, 'sort_order' => 1]);
        Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'category_id' => $category->id,
            'base_price' => 50000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Test Product');
        $response->assertSee('Test Store');
    }

    public function test_homepage_filters_by_category(): void
    {
        $cat1 = Category::create(['name' => 'Cat A', 'slug' => 'cat-a', 'is_active' => true, 'sort_order' => 1]);
        $cat2 = Category::create(['name' => 'Cat B', 'slug' => 'cat-b', 'is_active' => true, 'sort_order' => 2]);
        Product::create(['name' => 'Prod A', 'slug' => 'prod-a', 'category_id' => $cat1->id, 'base_price' => 10000, 'is_active' => true, 'sort_order' => 1]);
        Product::create(['name' => 'Prod B', 'slug' => 'prod-b', 'category_id' => $cat2->id, 'base_price' => 20000, 'is_active' => true, 'sort_order' => 2]);

        $response = $this->get('/?kategori=cat-a');
        $response->assertStatus(200);
        $response->assertSee('Prod A');
        $response->assertDontSee('Prod B');
    }

    public function test_best_customer_only_counts_completed_orders(): void
    {
        $pm = PaymentMethod::create(['name' => 'Transfer', 'type' => 'bank', 'is_active' => true, 'sort_order' => 1]);

        // Completed order
        Order::create([
            'order_code' => 'TS-AAAA0001',
            'customer_name' => 'Taufiq',
            'customer_phone' => '081234567890',
            'payment_method_id' => $pm->id,
            'subtotal' => 50000,
            'total' => 50000,
            'status' => 'completed',
            'ordered_at' => now(),
        ]);

        // Cancelled order (should NOT count)
        Order::create([
            'order_code' => 'TS-AAAA0002',
            'customer_name' => 'Budi',
            'customer_phone' => '089876543210',
            'payment_method_id' => $pm->id,
            'subtotal' => 30000,
            'total' => 30000,
            'status' => 'cancelled',
            'ordered_at' => now(),
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('T*****q');           // Taufiq masked
        $response->assertDontSee('B*****i');         // Budi should NOT appear
    }

    // ═══════════════════════════════════
    // PRODUCT DETAIL
    // ═══════════════════════════════════

    public function test_product_detail_returns_200(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true, 'sort_order' => 1]);
        $product = Product::create([
            'name' => 'My Product',
            'slug' => 'my-product',
            'category_id' => $category->id,
            'base_price' => 100000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/produk/my-product');
        $response->assertStatus(200);
        $response->assertSee('My Product');
        $response->assertSee('100.000');
    }

    public function test_product_detail_404_for_nonexistent(): void
    {
        $response = $this->get('/produk/nonexistent-slug');
        $response->assertStatus(404);
    }

    // ═══════════════════════════════════
    // TESTIMONIAL API
    // ═══════════════════════════════════

    public function test_validate_order_code_valid(): void
    {
        $pm = PaymentMethod::create(['name' => 'Transfer', 'type' => 'bank', 'is_active' => true, 'sort_order' => 1]);
        $order = Order::create([
            'order_code' => 'TS-TEST0001',
            'customer_name' => 'Ahmad',
            'customer_phone' => '081111111111',
            'payment_method_id' => $pm->id,
            'subtotal' => 50000,
            'total' => 50000,
            'status' => 'completed',
            'ordered_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Test Product',
            'variant_name' => null,
            'quantity' => 1,
            'price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->postJson('/api/testimonial/validate-order', ['order_code' => 'TS-TEST0001']);
        $response->assertStatus(200);
        $response->assertJson(['valid' => true, 'customer_name' => 'Ahmad']);
    }

    public function test_validate_order_code_invalid(): void
    {
        $response = $this->postJson('/api/testimonial/validate-order', ['order_code' => 'INVALID']);
        $response->assertStatus(200);
        $response->assertJson(['valid' => false]);
    }

    public function test_validate_order_code_already_used(): void
    {
        $pm = PaymentMethod::create(['name' => 'Transfer', 'type' => 'bank', 'is_active' => true, 'sort_order' => 1]);
        Order::create([
            'order_code' => 'TS-USED0001',
            'customer_name' => 'Ahmad',
            'customer_phone' => '081111111111',
            'payment_method_id' => $pm->id,
            'subtotal' => 50000,
            'total' => 50000,
            'status' => 'completed',
            'ordered_at' => now(),
        ]);
        Testimonial::create([
            'order_code' => 'TS-USED0001',
            'customer_name' => 'Ahmad',
            'rating' => 5,
            'content' => 'Great',
            'product_name' => 'Product',
            'status' => 'approved',
        ]);

        $response = $this->postJson('/api/testimonial/validate-order', ['order_code' => 'TS-USED0001']);
        $response->assertStatus(200);
        $response->assertJson(['valid' => false]);
    }

    public function test_submit_testimonial_success(): void
    {
        $pm = PaymentMethod::create(['name' => 'Transfer', 'type' => 'bank', 'is_active' => true, 'sort_order' => 1]);
        $order = Order::create([
            'order_code' => 'TS-SUB00001',
            'customer_name' => 'Siti',
            'customer_phone' => '082222222222',
            'payment_method_id' => $pm->id,
            'subtotal' => 75000,
            'total' => 75000,
            'status' => 'completed',
            'ordered_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Awesome Product',
            'quantity' => 1,
            'price' => 75000,
            'subtotal' => 75000,
        ]);

        $response = $this->postJson('/api/testimonial/submit', [
            'order_code' => 'TS-SUB00001',
            'rating' => 5,
            'content' => 'Sangat bagus!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('testimonials', [
            'order_code' => 'TS-SUB00001',
            'customer_name' => 'Siti',
            'rating' => 5,
            'status' => 'pending',
        ]);
    }

    // ═══════════════════════════════════
    // ARTICLES
    // ═══════════════════════════════════

    public function test_articles_page_returns_200(): void
    {
        $response = $this->get('/artikel');
        $response->assertStatus(200);
    }

    public function test_articles_shows_published_only(): void
    {
        Article::create([
            'title' => 'Published Article',
            'slug' => 'published',
            'content' => 'Content',
            'is_published' => true,
            'published_at' => now(),
        ]);
        Article::create([
            'title' => 'Draft Article',
            'slug' => 'draft',
            'content' => 'Content',
            'is_published' => false,
        ]);

        $response = $this->get('/artikel');
        $response->assertSee('Published Article');
        $response->assertDontSee('Draft Article');
    }

    public function test_article_detail_returns_200(): void
    {
        Article::create([
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => '<p>My content here</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/artikel/test-article');
        $response->assertStatus(200);
        $response->assertSee('Test Article');
        $response->assertSee('My content here');
    }

    public function test_article_detail_404_for_draft(): void
    {
        Article::create([
            'title' => 'Draft',
            'slug' => 'draft-slug',
            'content' => 'Content',
            'is_published' => false,
        ]);

        $response = $this->get('/artikel/draft-slug');
        $response->assertStatus(404);
    }

    // ═══════════════════════════════════
    // CART
    // ═══════════════════════════════════

    public function test_cart_page_returns_200(): void
    {
        $response = $this->get('/keranjang');
        $response->assertStatus(200);
    }

    // ═══════════════════════════════════
    // API ENDPOINTS
    // ═══════════════════════════════════

    public function test_search_products_api(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true, 'sort_order' => 1]);
        Product::create(['name' => 'Kaos Hitam', 'slug' => 'kaos-hitam', 'category_id' => $category->id, 'base_price' => 50000, 'is_active' => true, 'sort_order' => 1]);
        Product::create(['name' => 'Celana Jeans', 'slug' => 'celana-jeans', 'category_id' => $category->id, 'base_price' => 150000, 'is_active' => true, 'sort_order' => 2]);

        $response = $this->getJson('/api/products/search?q=Kaos');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_create_order_api(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true, 'sort_order' => 1]);
        $product = Product::create(['name' => 'Test', 'slug' => 'test-p', 'category_id' => $category->id, 'base_price' => 50000, 'is_active' => true, 'sort_order' => 1]);
        $pm = PaymentMethod::create(['name' => 'Transfer', 'type' => 'bank', 'is_active' => true, 'sort_order' => 1, 'admin_fee' => 0, 'fee_type' => 'fixed']);

        $response = $this->postJson('/api/order', [
            'customer_name' => 'John',
            'customer_phone' => '081234567890',
            'payment_method_id' => $pm->id,
            'items' => [
                ['product_id' => $product->id, 'variant_id' => null, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', ['customer_name' => 'John']);
    }

    // ═══════════════════════════════════
    // SITE MODE MIDDLEWARE
    // ═══════════════════════════════════

    public function test_live_mode_allows_access(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_maintenance_mode_redirects(): void
    {
        $setting = StoreSetting::current();
        $setting->update(['site_mode' => 'maintenance']);

        $response = $this->get('/');
        $response->assertRedirect(route('maintenance'));
    }

    public function test_coming_soon_mode_redirects(): void
    {
        $setting = StoreSetting::current();
        $setting->update(['site_mode' => 'coming_soon']);

        $response = $this->get('/');
        $response->assertRedirect(route('coming-soon'));
    }

    public function test_maintenance_page_redirects_when_live(): void
    {
        // Site is live, accessing /maintenance should redirect to home
        $response = $this->get('/maintenance');
        $response->assertRedirect(route('home'));
    }

    public function test_coming_soon_page_redirects_when_live(): void
    {
        $response = $this->get('/coming-soon');
        $response->assertRedirect(route('home'));
    }

    public function test_maintenance_page_accessible_when_maintenance(): void
    {
        StoreSetting::current()->update(['site_mode' => 'maintenance']);

        $response = $this->get('/maintenance');
        $response->assertStatus(200);
    }

    public function test_coming_soon_page_accessible_when_coming_soon(): void
    {
        StoreSetting::current()->update(['site_mode' => 'coming_soon']);

        $response = $this->get('/coming-soon');
        $response->assertStatus(200);
    }

    // ═══════════════════════════════════
    // FLASH SALE
    // ═══════════════════════════════════

    public function test_flash_sale_page_returns_200(): void
    {
        $response = $this->get('/flash-sale');
        $response->assertStatus(200);
    }
}
