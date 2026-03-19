<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::create([
            'store_name'       => 'Test Store',
            'whatsapp_number'  => '6281234567890',
            'site_mode'        => 'live',
            'message_template' => 'Test {items} {total} {payment} {name} {phone} {note} {order_code}',
        ]);
    }

    public function test_homepage_returns_200(): void
    {
        $category = Category::create(['name' => 'Test Cat', 'slug' => 'test-cat', 'is_active' => true, 'sort_order' => 1]);
        Product::create([
            'name'       => 'Test Product',
            'slug'       => 'test-product',
            'category_id' => $category->id,
            'base_price' => 50000,
            'is_active'  => true,
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
            'order_code'        => 'TS-AAAA0001',
            'customer_name'     => 'Taufiq',
            'customer_phone'    => '081234567890',
            'payment_method_id' => $pm->id,
            'subtotal'          => 50000,
            'total'             => 50000,
            'status'            => 'completed',
            'ordered_at'        => now(),
        ]);

        // Cancelled order (should NOT count)
        Order::create([
            'order_code'        => 'TS-AAAA0002',
            'customer_name'     => 'Budi',
            'customer_phone'    => '089876543210',
            'payment_method_id' => $pm->id,
            'subtotal'          => 30000,
            'total'             => 30000,
            'status'            => 'cancelled',
            'ordered_at'        => now(),
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('T*****q');       // Taufiq masked
        $response->assertDontSee('B*****i');   // Budi should NOT appear
    }
}
