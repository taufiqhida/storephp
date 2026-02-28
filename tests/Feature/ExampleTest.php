<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        StoreSetting::create([
            'store_name' => 'Test Store',
            'whatsapp_number' => '6281234567890',
            'site_mode' => 'live',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
