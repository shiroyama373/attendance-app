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
            'breaks_data.*.break_start' => 'required_with:breaks_data|date_format:H:i',
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
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 休憩時間の整合性チェック
            if ($this->breaks_data && $this->clock_in && $this->clock_out) {
                foreach ($this->breaks_data as $break) {
                    if (isset($break['break_start'])) {
                        // 休憩開始が出勤時間より前、または退勤時間より後
                        if ($break['break_start'] < $this->clock_in || $break['break_start'] > $this->clock_out) {
                            $validator->errors()->add('breaks_data', '休憩時間が不適切な値です');
                        }
                    }
                    if (isset($break['break_end']) && $this->clock_out) {
                        // 休憩終了が退勤時間より後
                        if ($break['break_end'] > $this->clock_out) {
                            $validator->errors()->add('breaks_data', '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }
                }
            }
        });
    }
}