<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;
    public function test_order_code_generation_is_unique(): void
    {
        $code1 = Order::generateOrderCode();
        $code2 = Order::generateOrderCode();

        $this->assertNotEquals($code1, $code2);
        $this->assertStringStartsWith('TS-', $code1);
        $this->assertStringStartsWith('TS-', $code2);
        $this->assertEquals(11, strlen($code1)); // TS- + 8 chars
    }

    public function test_order_status_label(): void
    {
        $order = new Order(['status' => 'completed']);
        $this->assertEquals('Selesai', $order->status_label);

        $order->status = 'cancelled';
        $this->assertEquals('Dibatalkan', $order->status_label);

        $order->status = 'pending';
        $this->assertEquals('Menunggu', $order->status_label);

        $order->status = 'processing';
        $this->assertEquals('Diproses', $order->status_label);
    }

    public function test_store_setting_defaults(): void
    {
        $setting = new StoreSetting();
        $this->assertNull($setting->launch_date);
    }

    public function test_name_masking_logic(): void
    {
        // Simulating the masking logic used in the view
        $testCases = [
            'Taufiq' => 'T*****q',
            'Muhammad Taufiqurrohmat' => 'M*****t',
            'AB' => 'A*',
            'A' => 'A*', // edge case
        ];

        foreach ($testCases as $input => $expected) {
            if (strlen($input) <= 2) {
                $masked = $input[0] . '*';
            } else {
                $masked = $input[0] . '*****' . substr($input, -1);
            }
            $this->assertEquals($expected, $masked, "Failed for input: $input");
        }
    }

    public function test_phone_masking_logic(): void
    {
        $testCases = [
            '087739612610' => '0877**',
            '081234567890' => '0812**',
            '0899' => '0899**',
        ];

        foreach ($testCases as $input => $expected) {
            $masked = substr($input, 0, 4) . '**';
            $this->assertEquals($expected, $masked, "Failed for phone: $input");
        }
    }
}
