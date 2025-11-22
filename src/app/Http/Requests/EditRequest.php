<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class EditRequest extends FormRequest
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
            'breaks.*.break_start' => 'nullable|date_format:H:i|before:breaks.*.break_end',
            'breaks.*.break_end' => 'nullable|date_format:H:i|after:breaks.*.break_start',
            'remarks' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時刻を入力してください',
            'start_time.date_format' => '<div class="up-position">出勤時刻の形式が正しくありません<br>入力例：09:00（半角で入力）</div>',

            'end_time.required' => '退勤時刻を入力してください',
            'end_time.date_format' => '<div class="up-position">退勤時刻の形式が正しくありません<br>入力例：09:00（半角で入力）</div>',
            'end_time.after' => '<div class="up-position">出勤時間もしくは退勤時間が<br>不適切な値です</div>',

            'breaks.*.break_start.date_format' => '<div class="up-position">休憩開始時刻の形式が正しくありません<br>入力例：09:00（半角で入力）</div>',
            'breaks.*.break_start.before' => '休憩時間が不適切な値です',

            'breaks.*.break_end.date_format' => '<div class="up-position">休憩終了時刻の形式が正しくありません<br>入力例：09:00（半角で入力）</div>',
            'breaks.*.break_end.after' => '<div class="up-position">出勤時間もしくは退勤時間が<br>不適切な値です</div>',

            'remarks.required' => '備考を記入してください',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startInput = $this->input('start_time');
            $endInput = $this->input('end_time');
            $workStart = ($startInput !== null && $startInput !== '' && preg_match('/^\d{2}:\d{2}$/', $startInput))
                ? Carbon::createFromFormat('H:i', $startInput)
                : null;
            $workEnd = ($endInput !== null && $endInput !== '' && preg_match('/^\d{2}:\d{2}$/', $endInput))
                ? Carbon::createFromFormat('H:i', $endInput)
                : null;
            $breaks = $this->input('breaks', []);
            foreach ($breaks as $index => $break) {
                $breakStart = $break['break_start'] ?? null;
                $breakEnd   = $break['break_end'] ?? null;
                // どちらも入力されていなければスキップ
                if (!$breakStart && !$breakEnd) {
                    continue;
                }
                // 片方だけ入力されている場合はエラーにする
                if (!$breakStart && $breakEnd) {
                    $validator->errors()->add("breaks.$index.break_start", '休憩開始時刻を入力してください');
                } elseif ($breakStart && !$breakEnd) {
                    $validator->errors()->add("breaks.$index.break_end", '休憩終了時刻を入力してください');
                }
                if (preg_match('/^\d{2}:\d{2}$/', $breakStart) && preg_match('/^\d{2}:\d{2}$/', $breakEnd)) {
                    $breakStartTime = Carbon::createFromFormat('H:i', $breakStart);
                    $breakEndTime = Carbon::createFromFormat('H:i', $breakEnd);
                    if ($breakEndTime->gt($workEnd)) {
                        $validator->errors()->add("breaks.$index.break_end", '<div class="up-position">休憩時間もしくは退勤時間が<br>不適切な値です</div>');
                    }
                    if ($breakStartTime->lt($workStart)) {
                        $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                    }
                }
            }
        });
    }
}
