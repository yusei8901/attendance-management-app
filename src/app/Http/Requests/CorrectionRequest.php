<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
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
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'breaks.*.break_start' => 'nullable|date_format:H:i|after:start_time|before:breaks.*.break_end',
            'breaks.*.break_end' => 'nullable|date_format:H:i|after:break_start|before:end_time',
            'remarks' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時刻を入力してください',
            'start_time.date_format' => '<div class="up-position">出勤時刻の形式が正しくありません<br>（入力例：09:00）</div>',

            'end_time.required' => '退勤時刻を入力してください',
            'end_time.date_format' => '<div class="up-position">退勤時刻の形式が正しくありません<br>（入力例：09:00）</div>',
            'end_time.after' => '<div class="up-position">出勤時間もしくは退勤時間が<br>不適切な値です</div>',

            'breaks.*.break_start.date_format' => '<div class="up-position">休憩開始時刻の形式が正しくありません<br>（入力例：09:00）</div>',
            'breaks.*.break_start.after' => '休憩時間が不適切な値です',
            'breaks.*.break_start.before' => '休憩時間が不適切な値です',
            'breaks.*.break_end.date_format' => '<div class="up-position">休憩終了時刻の形式が正しくありません<br>（入力例：09:00）</div>',
            'breaks.*.break_end.after' => '休憩時間が不適切な値です',
            'breaks.*.break_end.before' => '<div class="up-position">休憩時間もしくは退勤時間が<br>不適切な値です</div>',

            'remarks.required' => '備考を記入してください',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 休憩開始だけ入力された場合
            if ($this->break_start && !$this->break_end) {
                $validator->errors()->add('break_end', '休憩終了時刻を入力してください');
            }
            // 休憩終了だけ入力された場合
            if (!$this->break_start && $this->break_end) {
                $validator->errors()->add('break_start', '休憩開始時刻を入力してください');
            }
        });
    }
}
