<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_page_is_displayed(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('テストカテゴリ');
        $response->assertSee('テストタグ');
    }

    public function test_thanks_page_is_displayed(): void
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);
    }
}
