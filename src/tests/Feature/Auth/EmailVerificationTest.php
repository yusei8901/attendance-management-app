<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // 会員登録後、認証メールが送信される
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $response = $this->post(route('user.register'), [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(302);
        $user = User::where('email', 'test@example.com')->firstOrFail();

        // VerifyEmail 通知が送られたことを確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    // メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function test_clicking_verify_button_on_verification_prompt_redirects_to_verification_notice_page()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->unverified()->create();

        $this->actingAs($user, 'web');

        // 誘導画面
        $notice = $this->get(route('verification.notice'));
        $notice->assertStatus(200);
        $notice->assertViewIs('user.auth.verify-email');

        // 文言があること（ボタン/リンクの存在チェック）
        $notice->assertSee('認証はこちらから');

        // 画面から実際にクリックはできないので、遷移先URLが含まれる想定で確認（Bladeが <a href="..."> の場合）
        $notice->assertSee(route('verification.confirm'), false);

        // 「押下後」をGETで表現
        $confirm = $this->get(route('verification.confirm'));
        $confirm->assertStatus(200);
        $confirm->assertViewIs('user.auth.verify-confirm');
        $confirm->assertViewHas('verificationUrl');
    }

    // メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
    public function test_after_email_verification_user_is_redirected_to_attendance_create_page()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->unverified()->create();
        $this->actingAs($user, 'web');

        // Controller(confirm)と同じ作り方で署名付きURLを生成して叩く
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($signedUrl);

        $response->assertRedirect('/attendance');
        $response->assertSessionHas('verify_success', 'メール認証が完了しました');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
