<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_name_as_current_tag_passes_validation(): void
    {
        $tag = Tag::create([
            'name' => '重要',
        ]);

        $request = new UpdateTagRequest;
        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter($key)
                {
                    return $key === 'tag' ? $this->tag : null;
                }
            };
        });

        $validator = Validator::make([
            'name' => '重要',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_name_used_by_another_tag_fails_validation(): void
    {
        $tag = Tag::create([
            'name' => '重要',
        ]);

        Tag::create([
            'name' => '緊急',
        ]);

        $request = new UpdateTagRequest;
        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter($key)
                {
                    return $key === 'tag' ? $this->tag : null;
                }
            };
        });

        $validator = Validator::make([
            'name' => '緊急',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }
}
