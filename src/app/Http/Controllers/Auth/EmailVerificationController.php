<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    // 未認証ユーザーへの誘導画面
    public function notice()
    {
        return view('user.auth.verify-email');
    }

    // 認証メール再送
    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resend_message', '認証メールを再送しました。');
    }

    public function confirm()
    {
        $user = auth()->user();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60), //有効期限（60分）
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        return view('user.auth.verify-confirm', [
            'verificationUrl' => $verificationUrl,
        ]);
    }

    // 認証リンクをクリックしたとき
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill(); // 認証完了
        return redirect('/attendance')->with('verify_success', 'メール認証が完了しました');
    }
}
