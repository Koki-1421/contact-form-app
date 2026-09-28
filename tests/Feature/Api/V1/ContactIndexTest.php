<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_can_be_listed_with_pagination(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        for ($i = 1; $i <= 12; $i++) {
            Contact::create([
                'first_name' => '太郎',
                'last_name' => "テスト{$i}",
                'gender' => 1,
                'email' => "test{$i}@example.com",
                'tel' => '09012345678',
                'address' => '東京都新宿区1-2-3',
                'category_id' => $category->id,
                'detail' => 'お問い合わせ内容です',
            ]);
        }

        $response = $this->getJson('/api/v1/contacts?per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 12);
    }

    public function test_contacts_can_be_searched(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-2-3',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        Contact::create([
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区4-5-6',
            'category_id' => $category->id,
            'detail' => '別のお問い合わせです',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=山田&gender=1');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.last_name', '山田')
            ->assertJsonPath('data.0.gender', 1);
    }

    public function test_invalid_search_parameters_return_422(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=999');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender'])
            ->assertJsonPath('errors.gender.0', '性別の値が不正です');
    }

    public function test_contacts_can_be_searched_by_category_and_date(): void
    {
        $category1 = Category::create([
            'content' => 'カテゴリ1',
        ]);

        $category2 = Category::create([
            'content' => 'カテゴリ2',
        ]);

        $contact1 = Contact::create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-2-3',
            'category_id' => $category1->id,
            'detail' => '対象のお問い合わせです',
        ]);

        $contact1->forceFill([
            'created_at' => '2026-09-27 10:00:00',
            'updated_at' => '2026-09-27 10:00:00',
        ])->save();

        $contact2 = Contact::create([
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区4-5-6',
            'category_id' => $category2->id,
            'detail' => '対象外のお問い合わせです',
        ]);

        $contact2->forceFill([
            'created_at' => '2026-09-26 10:00:00',
            'updated_at' => '2026-09-26 10:00:00',
        ])->save();

        $response = $this->getJson(
            "/api/v1/contacts?category_id={$category1->id}&date=2026-09-27"
        );

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.last_name', '山田')
            ->assertJsonPath('data.0.category.id', $category1->id);
    }
}
