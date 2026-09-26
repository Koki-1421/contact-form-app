<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_belongs_to_category(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'detail' => 'お問い合わせ',
        ]);

        $this->assertEquals($category->id, $contact->category->id);
        $this->assertEquals('テストカテゴリ', $contact->category->content);
    }

    public function test_contact_can_sync_multiple_tags(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'detail' => 'お問い合わせ',
        ]);

        $tag1 = Tag::create([
            'name' => '重要',
        ]);

        $tag2 = Tag::create([
            'name' => '緊急',
        ]);

        $contact->tags()->sync([
            $tag1->id,
            $tag2->id,
        ]);

        $this->assertCount(2, $contact->tags);
        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
    }
}
