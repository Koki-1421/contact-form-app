<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_contact_data_is_stored(): void
    {
        $category = Category::create([
            'content' => '商品について',
        ]);

        $tag = Tag::create([
            'name' => '重要',
        ]);

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'テストのお問い合わせです。',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->post('/contacts', $data);

        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'taro@example.com',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'tag_id' => $tag->id,
        ]);

        $response->assertRedirect('/thanks');
    }

    public function test_invalid_contact_data_is_not_stored(): void
    {
        $response = $this->post('/contacts', []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);

        $this->assertDatabaseCount('contacts', 0);
    }
}
