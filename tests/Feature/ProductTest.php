<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
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

    public function test_product_detail_returns_200(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true, 'sort_order' => 1]);
        Product::create([
            'name'       => 'My Product',
            'slug'       => 'my-product',
            'category_id' => $category->id,
            'base_price' => 100000,
            'is_active'  => true,
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
}
