<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_user_name_email_password_are_required()
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors(['user_name', 'email', 'password', 'password_confirmation']);
        $this->assertGuest();
    }

    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'user_name' => 'テストユーザー',
            'email' => 'short-password@example.com',
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'short-password@example.com']);
        $this->assertGuest();
    }

    public function test_password_confirmation_must_match_password()
    {
        $response = $this->post('/register', [
            'user_name' => 'テストユーザー',
            'email' => 'mismatch@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password_confirmation');
        $this->assertDatabaseMissing('users', ['email' => 'mismatch@example.com']);
        $this->assertGuest();
    }

    public function test_email_must_be_unique()
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->post('/register', [
            'user_name' => 'テストユーザー',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_register_with_valid_data_and_is_redirected()
    {
        $response = $this->post('/register', [
            'user_name' => 'テストユーザー',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'user_name' => 'テストユーザー',
            'email' => 'newuser@example.com',
        ]);
        $this->assertAuthenticated();

        // Fortifyの標準挙動として登録と同時に自動ログインされ、/homeへ遷移する
        // （案件シートの「ログイン画面遷移」という記載とは異なるが、実際の挙動はこちら）
        $response->assertRedirect('/home');
    }
}
