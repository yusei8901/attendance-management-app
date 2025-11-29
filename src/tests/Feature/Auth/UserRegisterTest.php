<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 名前が未入力の場合、バリデーションメッセージが表示される
    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    // お名前が20文字以上で入力された場合、バリデーションメッセージが表示される
    public function test_name_is_invalid_when_longer_than_20_characters()
    {
        $longName = str_repeat('あ', 21);
        $response = $this->post('/register', [
            'name' => $longName,
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['name' => '20文字以内で入力してください']);
    }

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    // メールアドレスがメール形式で入力されていない場合、バリデーションメッセージが表示される
    public function test_validation_error_is_displayed_when_email_is_not_in_valid_format()
    {
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'aaa',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email' => 'メールアドレスはメール形式で入力してください']);
    }

    // すでにメールアドレスが使用されている場合、バリデーションメッセージが表示される
    public function test_validation_error_is_displayed_when_email_is_already_taken()
    {
        // 既存ユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com',
        ]);
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email' => '既に使用されているメールアドレスです']);
    }

    // パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    // パスワードが一致しない場合、バリデーションメッセージが表示される
    public function test_password_confirmation_must_match()
    {
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password12',
        ]);
        $response->assertSessionHasErrors(['password_confirmation' => 'パスワードと一致しません']);
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    // フォームに内容が入力されていた場合、データが正常に保存される
    public function test_user_can_register_with_valid_data()
    {
        $email = 'test' . uniqid() . '@example.com';
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'name' => 'testuser',
            'email' => $email,
        ]);
        $user = User::where('email', $email)->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
