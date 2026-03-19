<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\StoreSetting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
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

    public function test_validate_order_code_valid(): void
    {
        $pm = PaymentMethod::create(['name' => 'Transfer', 'type' => 'bank', 'is_active' => true, 'sort_order' => 1]);
        $order = Order::create([
            'order_code'        => 'TS-TEST0001',
            'customer_name'     => 'Ahmad',
            'customer_phone'    => '081111111111',
            'payment_method_id' => $pm->id,
            'subtotal'          => 50000,
            'total'             => 50000,
            'status'            => 'completed',
            'ordered_at'        => now(),
        ]);
        OrderItem::create([
            'order_id'     => $order->id,
            'product_name' => 'Test Product',
            'variant_name' => null,
            'quantity'     => 1,
            'price'        => 50000,
            'subtotal'     => 50000,
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
            'order_code'        => 'TS-USED0001',
            'customer_name'     => 'Ahmad',
            'customer_phone'    => '081111111111',
            'payment_method_id' => $pm->id,
            'subtotal'          => 50000,
            'total'             => 50000,
            'status'            => 'completed',
            'ordered_at'        => now(),
        ]);
        Testimonial::create([
            'order_code'    => 'TS-USED0001',
            'customer_name' => 'Ahmad',
            'rating'        => 5,
            'content'       => 'Great',
            'product_name'  => 'Product',
            'status'        => 'approved',
        ]);

        $response = $this->postJson('/api/testimonial/validate-order', ['order_code' => 'TS-USED0001']);
        $response->assertStatus(200);
        $response->assertJson(['valid' => false]);
    }

    public function test_submit_testimonial_success(): void
    {
        $pm = PaymentMethod::create(['name' => 'Transfer', 'type' => 'bank', 'is_active' => true, 'sort_order' => 1]);
        $order = Order::create([
            'order_code'        => 'TS-SUB00001',
            'customer_name'     => 'Siti',
            'customer_phone'    => '082222222222',
            'payment_method_id' => $pm->id,
            'subtotal'          => 75000,
            'total'             => 75000,
            'status'            => 'completed',
            'ordered_at'        => now(),
        ]);
        OrderItem::create([
            'order_id'     => $order->id,
            'product_name' => 'Awesome Product',
            'quantity'     => 1,
            'price'        => 75000,
            'subtotal'     => 75000,
        ]);

        $response = $this->postJson('/api/testimonial/submit', [
            'order_code' => 'TS-SUB00001',
            'rating'     => 5,
            'content'    => 'Sangat bagus!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('testimonials', [
            'order_code'    => 'TS-SUB00001',
            'customer_name' => 'Siti',
            'rating'        => 5,
            'status'        => 'pending',
        ]);
    }
}
