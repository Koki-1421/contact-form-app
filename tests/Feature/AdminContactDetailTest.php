<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_detail_page_is_displayed(): void
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
            'detail' => '商品の詳細について知りたいです。',
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/contacts/'.$contact->id);

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertSee('yamada@example.com');
        $response->assertSee('商品について');
    }
}
