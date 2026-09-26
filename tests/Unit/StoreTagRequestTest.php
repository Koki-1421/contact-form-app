<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_name_fails_validation(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make([
            'name' => '',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_name_over_50_characters_fails_validation(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make([
            'name' => str_repeat('あ', 51),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_duplicate_name_fails_validation(): void
    {
        Tag::create([
            'name' => '重要',
        ]);

        $request = new StoreTagRequest;

        $validator = Validator::make([
            'name' => '重要',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }
}
