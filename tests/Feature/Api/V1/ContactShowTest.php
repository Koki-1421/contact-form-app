<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_can_be_shown(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $contact = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-2-3',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonPath('data.last_name', '山田')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.category.content', 'テストカテゴリ')
            ->assertJsonPath('data.tags.0.id', $tag->id)
            ->assertJsonPath('data.tags.0.name', 'テストタグ');
    }

    public function test_nonexistent_contact_returns_404(): void
    {
        $response = $this->getJson('/api/v1/contacts/999999');

        $response->assertStatus(404);
    }
}
