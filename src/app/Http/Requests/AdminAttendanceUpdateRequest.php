<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
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
            'clock_in.date_format' =>'出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format' =>'出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
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
        if ($this->breaks_data && $this->clock_in && $this->clock_out) {
            try {
                $clockIn = Carbon::createFromFormat('H:i', $this->clock_in);
                $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);
            } catch (\Exception $e) {
                return; // パース失敗したら終了
            }
            
foreach ($this->breaks_data as $index => $break) {
    // 空の休憩データはスキップ
    if (empty($break['break_start']) && empty($break['break_end'])) {
        continue;
    }
    
    try {
        // 休憩開始と終了の順序チェック（最優先）
        if (!empty($break['break_start']) && !empty($break['break_end'])) {
            $breakStart = Carbon::createFromFormat('H:i', $break['break_start']);
            $breakEnd = Carbon::createFromFormat('H:i', $break['break_end']);
            
            if ($breakEnd->lte($breakStart)) {
                $validator->errors()->add('breaks_data.'.$index.'.break_end', '休憩時間が不適切な値です');
                continue;  // 他のチェックをスキップ
            }
        }
        
        // 休憩開始時間のチェック
        if (!empty($break['break_start'])) {
            $breakStart = Carbon::createFromFormat('H:i', $break['break_start']);
            
            if ($breakStart->lt($clockIn) || $breakStart->gt($clockOut)) {
                $validator->errors()->add('breaks_data.'.$index.'.break_start', '休憩時間が不適切な値です');
                continue;  // 他のチェックをスキップ
            }
        }
        
        // 休憩終了時間のチェック
        if (!empty($break['break_end'])) {
            $breakEnd = Carbon::createFromFormat('H:i', $break['break_end']);
            
            if ($breakEnd->gt($clockOut)) {
                $validator->errors()->add('breaks_data.'.$index.'.break_end', '休憩時間もしくは退勤時間が不適切な値です');
            }
        }
        
    } catch (\Exception $e) {
        continue;
    }
}
}
    });
}
}  // ← クラスの閉じ括弧