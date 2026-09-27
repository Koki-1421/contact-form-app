<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_can_be_created(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $tag1 = Tag::create([
            'name' => 'タグ1',
        ]);

        $tag2 = Tag::create([
            'name' => 'タグ2',
        ]);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-2-3',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag1->id, $tag2->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', '太郎')
            ->assertJsonPath('data.last_name', '山田')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonCount(2, 'data.tags');

        $this->assertDatabaseHas('contacts', [
            'email' => 'taro@example.com',
            'category_id' => $category->id,
        ]);

        $contactId = $response->json('data.id');

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contactId,
            'tag_id' => $tag1->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contactId,
            'tag_id' => $tag2->id,
        ]);
    }

    public function test_invalid_contact_data_returns_422(): void
    {
        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 999,
            'email' => 'taro@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都新宿区1-2-3',
            'category_id' => 999999,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [999999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'gender',
                'tel',
                'category_id',
                'tag_ids.0',
            ])
            ->assertJsonPath(
                'errors.tel.0',
                '電話番号はハイフンなしの10〜11桁で入力してください'
            )
            ->assertJsonPath(
                'errors.gender.0',
                '性別の値が不正です'
            )
            ->assertJsonPath(
                'errors.category_id.0',
                '選択されたカテゴリーが存在しません'
            );

        $this->assertEquals(
            '選択されたタグが存在しません',
            $response->json('errors')['tag_ids.0'][0]
        );
    }
}
