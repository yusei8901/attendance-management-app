<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123'
        ]);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_required()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => ''
        ]);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_login_fails_with_invalid_email()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    // 正しい情報が入力された場合、ログイン処理が実行される
    public function test_valid_credentials_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);
        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/attendance');
    }
}
