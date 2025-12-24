<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendance_id' => 'required|exists:attendances,id',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'breaks_data' => 'nullable|array',
            'breaks_data.*.break_start' => 'nullable|date_format:H:i',
            'breaks_data.*.break_end' => 'nullable|date_format:H:i',
            'note' => 'required|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
            'clock_in.date_format' => '出勤時間が不適切な値です',
            'clock_out.date_format' => '出勤時間が不適切な値です',
            'clock_out.after' => '出勤時間が不適切な値です',
            'breaks_data.*.break_start.date_format' => '休憩時間が不適切な値です',
            'breaks_data.*.break_end.date_format' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'attendance_id' => '勤怠ID',
            'clock_in' => '出勤時間',
            'clock_out' => '退勤時間',
            'breaks_data.*.break_start' => '休憩開始時間',
            'breaks_data.*.break_end' => '休憩終了時間',
            'note' => '備考',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // 休憩時間の整合性チェック
        if ($this->breaks_data) {
            foreach ($this->breaks_data as $index => $break) {
                // 空の休憩データはスキップ
                if (empty($break['break_start']) && empty($break['break_end'])) {
                    continue;
                }
                
                // 休憩開始時間のチェック
                if (!empty($break['break_start'])) {
                    // 出勤時間が入力されている場合、休憩開始が出勤時間より前ならエラー
                    if (!empty($this->clock_in) && $break['break_start'] < $this->clock_in) {
                        $validator->errors()->add('breaks_data.'.$index.'.break_start', '休憩時間が不適切な値です');
                    }
                    
                    // 退勤時間が入力されている場合、休憩開始が退勤時間より後ならエラー
                    if (!empty($this->clock_out) && $break['break_start'] > $this->clock_out) {
                        $validator->errors()->add('breaks_data.'.$index.'.break_start', '休憩時間が不適切な値です');
                    }
                }
                
                // 休憩終了時間のチェック
                if (!empty($break['break_end']) && !empty($this->clock_out)) {
                    // 休憩終了が退勤時間より後ならエラー
                    if ($break['break_end'] > $this->clock_out) {
                        $validator->errors()->add('breaks_data.'.$index.'.break_end', '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        }
    });
}
}