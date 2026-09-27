<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_search_parameters_pass_validation(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $request = new IndexContactRequest;

        $validator = Validator::make([
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-09-27',
            'per_page' => 10,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_search_parameters_fail_validation(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make([
            'gender' => 999,
            'category_id' => 999999,
            'date' => 'invalid-date',
            'per_page' => 0,
        ], $request->rules());

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }
}
