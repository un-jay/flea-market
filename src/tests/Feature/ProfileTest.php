<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_screen_shows_current_values()
    {
        $user = User::factory()->create([
            'user_name' => '編集前の名前',
            'postal_code' => '106-6118',
            'address' => '東京都港区六本木6-10-1',
            'building' => '六本木ヒルズ森タワー',
        ]);

        $response = $this->actingAs($user)->get('/mypage/profile');

        $response->assertStatus(200);
        $response->assertSee('value="編集前の名前"', false);
        $response->assertSee('value="106-6118"', false);
        $response->assertSee('value="東京都港区六本木6-10-1"', false);
        $response->assertSee('value="六本木ヒルズ森タワー"', false);
    }

    public function test_user_can_update_profile_without_changing_image()
    {
        $user = User::factory()->create(['user_name' => '編集前の名前']);

        $response = $this->actingAs($user)->post('/mypage/profile/upload', [
            'user_name' => '編集後の名前',
            'postal_code' => '150-0001',
            'address' => '東京都渋谷区神宮前1-1-1',
            'building' => 'テストビル101',
        ]);

        $response->assertRedirect('/mypage?tab=sell');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'user_name' => '編集後の名前',
            'postal_code' => '150-0001',
        ]);
    }

    public function test_user_can_update_profile_with_new_image()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/mypage/profile/upload', [
            'user_name' => '編集後の名前',
            'postal_code' => '150-0001',
            'address' => '東京都渋谷区神宮前1-1-1',
            'building' => 'テストビル101',
            'profile_image' => UploadedFile::fake()->image('profile.jpg'),
        ]);

        $response->assertRedirect('/mypage?tab=sell');
        $user->refresh();
        $this->assertStringContainsString('profile.jpg', $user->profile_image);
    }

    public function test_profile_update_requires_postal_code_in_correct_format()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/mypage/profile/upload', [
            'user_name' => '編集後の名前',
            'postal_code' => '1234567',
            'address' => '東京都渋谷区神宮前1-1-1',
            'building' => '',
        ]);

        $response->assertSessionHasErrors('postal_code');
    }
}
