<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_tag_edit_page(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '重要',
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/tags/'.$tag->id.'/edit');

        $response->assertStatus(200);
        $response->assertSee('重要');
    }

    public function test_authenticated_user_can_create_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/admin/tags', [
                'name' => '新規タグ',
            ]);

        $this->assertDatabaseHas('tags', [
            'name' => '新規タグ',
        ]);

        $response->assertRedirect('/admin');
    }

    public function test_authenticated_user_can_update_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '変更前',
        ]);

        $response = $this->actingAs($user)
            ->put('/admin/tags/'.$tag->id, [
                'name' => '変更後',
            ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '変更後',
        ]);

        $response->assertRedirect('/admin');
    }

    public function test_authenticated_user_can_delete_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '削除対象',
        ]);

        $response = $this->actingAs($user)
            ->delete('/admin/tags/'.$tag->id);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);

        $response->assertRedirect('/admin');
    }

    public function test_guest_cannot_manage_tags(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $editResponse = $this->get('/admin/tags/'.$tag->id.'/edit');

        $createResponse = $this->post('/admin/tags', [
            'name' => '新規タグ',
        ]);

        $updateResponse = $this->put('/admin/tags/'.$tag->id, [
            'name' => '変更後',
        ]);

        $deleteResponse = $this->delete('/admin/tags/'.$tag->id);

        $editResponse->assertRedirect('/login');
        $createResponse->assertRedirect('/login');
        $updateResponse->assertRedirect('/login');
        $deleteResponse->assertRedirect('/login');
    }
}
