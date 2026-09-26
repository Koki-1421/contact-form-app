<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_search_parameters_pass_validation(): void
    {
        $request = new IndexContactRequest;

        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'date' => '2026-09-26',
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_gender_fails_validation(): void
    {
        $request = new IndexContactRequest;

        $data = [
            'gender' => 99,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('gender'));
    }

    public function test_existing_category_id_passes_validation(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $request = new IndexContactRequest;

        $data = [
            'category_id' => $category->id,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_non_existing_category_id_fails_validation(): void
    {
        $request = new IndexContactRequest;

        $data = [
            'category_id' => 999999,
        ];

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('category_id'));
    }
}
