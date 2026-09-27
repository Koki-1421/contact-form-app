<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/contacts/export');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_download_csv(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/contacts/export');

        $response->assertStatus(200);
        $response->assertDownload('contacts.csv');
    }

    public function test_csv_can_be_filtered_by_keyword(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'detail' => 'お問い合わせ1',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'detail' => 'お問い合わせ2',
        ]);

        $response = $this->actingAs($user)
            ->get('/contacts/export?keyword=山田');

        $response->assertStatus(200);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('山田', $content);
        $this->assertStringNotContainsString('佐藤', $content);
    }

    public function test_csv_can_be_filtered_by_multiple_conditions(): void
    {
        $user = User::factory()->create();

        $category1 = Category::create([
            'content' => '商品について',
        ]);

        $category2 = Category::create([
            'content' => 'その他',
        ]);

        Contact::create([
            'category_id' => $category1->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'detail' => 'お問い合わせ1',
        ]);

        Contact::create([
            'category_id' => $category1->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'detail' => 'お問い合わせ2',
        ]);

        Contact::create([
            'category_id' => $category2->id,
            'first_name' => '鈴木',
            'last_name' => '次郎',
            'gender' => 1,
            'email' => 'suzuki@example.com',
            'tel' => '07012345678',
            'address' => '東京都中野区',
            'detail' => 'お問い合わせ3',
        ]);

        $response = $this->actingAs($user)
            ->get('/contacts/export?gender=1&category_id='.$category1->id);

        $response->assertStatus(200);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('山田', $content);
        $this->assertStringNotContainsString('佐藤', $content);
        $this->assertStringNotContainsString('鈴木', $content);
    }

    public function test_csv_can_be_filtered_by_date(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        $contact1 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'detail' => 'お問い合わせ1',
        ]);

        $contact2 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'detail' => 'お問い合わせ2',
        ]);

        $contact1->created_at = '2026-09-20 10:00:00';
        $contact1->save();

        $contact2->created_at = '2026-09-21 10:00:00';
        $contact2->save();

        $response = $this->actingAs($user)
            ->get('/contacts/export?date=2026-09-20');

        $response->assertStatus(200);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('山田', $content);
        $this->assertStringNotContainsString('佐藤', $content);
    }

    public function test_csv_is_ordered_by_newest_when_no_filters_are_given(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        $oldContact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '古い',
            'last_name' => '問い合わせ',
            'gender' => 1,
            'email' => 'old@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'detail' => '古いお問い合わせ',
        ]);

        $newContact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '新しい',
            'last_name' => '問い合わせ',
            'gender' => 1,
            'email' => 'new@example.com',
            'tel' => '08012345678',
            'address' => '東京都渋谷区',
            'detail' => '新しいお問い合わせ',
        ]);

        $oldContact->created_at = '2026-09-20 10:00:00';
        $oldContact->save();

        $newContact->created_at = '2026-09-21 10:00:00';
        $newContact->save();

        $response = $this->actingAs($user)
            ->get('/contacts/export');

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $newPosition = strpos($content, '新しい');
        $oldPosition = strpos($content, '古い');

        $this->assertNotFalse($newPosition);
        $this->assertNotFalse($oldPosition);
        $this->assertLessThan($oldPosition, $newPosition);
    }

    public function test_csv_has_bom_and_correct_header(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/contacts/export');

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        $header = 'ID,氏名,性別,メール,電話,住所,建物,カテゴリ,内容,作成日時';

        $this->assertStringContainsString($header, $content);
    }
}
