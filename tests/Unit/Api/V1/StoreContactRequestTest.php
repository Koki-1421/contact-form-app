<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_contact_data_passes_validation(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $request = new StoreContactRequest;

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-2-3',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_contact_data_fails_validation(): void
    {
        $request = new StoreContactRequest;

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 999,
            'email' => 'test@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都新宿区1-2-3',
            'category_id' => 999999,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [999999],
        ], $request->rules());

        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('tel', $errors);
        $this->assertArrayHasKey('category_id', $errors);
        $this->assertArrayHasKey('tag_ids.0', $errors);
    }
}
