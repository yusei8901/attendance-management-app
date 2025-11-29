<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\EmailFormatRule;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class CustomLoginRequest extends FortifyLoginRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'email', new EmailFormatRule()],
            'password' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスの形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            // 入力ルールエラーがない場合のみ処理
            if (! $validator->errors()->any()) {
                $credentials = $this->only('email', 'password');
                if ($this->is('admin/*')) {
                    if (! Auth::guard('admin')->validate($credentials)) {
                        $validator->errors()->add('email', 'ログイン情報が登録されていません');
                    }
                } else {
                    if (! Auth::guard('web')->validate($credentials)) {
                        $validator->errors()->add('email', 'ログイン情報が登録されていません');
                    }
                }
            }
        });
    }
}
