<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_can_be_updated(): void
    {
        $category1 = Category::create([
            'content' => 'カテゴリ1',
        ]);

        $category2 = Category::create([
            'content' => 'カテゴリ2',
        ]);

        $tag1 = Tag::create([
            'name' => 'タグ1',
        ]);

        $tag2 = Tag::create([
            'name' => 'タグ2',
        ]);

        $contact = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-2-3',
            'building' => '更新前ビル',
            'category_id' => $category1->id,
            'detail' => '更新前のお問い合わせです',
        ]);

        $contact->tags()->attach($tag1->id);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区4-5-6',
            'building' => '更新後ビル',
            'category_id' => $category2->id,
            'detail' => '更新後のお問い合わせです',
            'tag_ids' => [$tag2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', '花子')
            ->assertJsonPath('data.last_name', '佐藤')
            ->assertJsonPath('data.category.id', $category2->id)
            ->assertJsonCount(1, 'data.tags')
            ->assertJsonPath('data.tags.0.id', $tag2->id);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'email' => 'hanako@example.com',
            'category_id' => $category2->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag2->id,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag1->id,
        ]);
    }

    public function test_nonexistent_contact_returns_404(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $response = $this->putJson('/api/v1/contacts/999999', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-2-3',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        $response->assertStatus(404);
    }

    public function test_invalid_contact_data_returns_422(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $contact = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-2-3',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
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
