<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteModeTest extends TestCase
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

    public function test_live_mode_allows_access(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_maintenance_mode_redirects(): void
    {
        StoreSetting::current()->update(['site_mode' => 'maintenance']);

        $response = $this->get('/');
        $response->assertRedirect(route('maintenance'));
    }

    public function test_coming_soon_mode_redirects(): void
    {
        StoreSetting::current()->update(['site_mode' => 'coming_soon']);

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
}
