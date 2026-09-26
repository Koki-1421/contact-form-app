<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'detail' => '削除テストです。',
        ]);

        $response = $this->actingAs($user)
            ->delete('/admin/contacts/'.$contact->id);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);

        $response->assertRedirect('/admin');
    }
}
