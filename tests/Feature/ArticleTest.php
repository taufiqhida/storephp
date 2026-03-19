<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
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

    public function test_articles_page_returns_200(): void
    {
        $response = $this->get('/artikel');
        $response->assertStatus(200);
    }

    public function test_articles_shows_published_only(): void
    {
        Article::create([
            'title'        => 'Published Article',
            'slug'         => 'published',
            'content'      => 'Content',
            'is_published' => true,
            'published_at' => now(),
        ]);
        Article::create([
            'title'        => 'Draft Article',
            'slug'         => 'draft',
            'content'      => 'Content',
            'is_published' => false,
        ]);

        $response = $this->get('/artikel');
        $response->assertSee('Published Article');
        $response->assertDontSee('Draft Article');
    }

    public function test_article_detail_returns_200(): void
    {
        Article::create([
            'title'        => 'Test Article',
            'slug'         => 'test-article',
            'content'      => '<p>My content here</p>',
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
            'title'        => 'Draft',
            'slug'         => 'draft-slug',
            'content'      => 'Content',
            'is_published' => false,
        ]);

        $response = $this->get('/artikel/draft-slug');
        $response->assertStatus(404);
    }
}
