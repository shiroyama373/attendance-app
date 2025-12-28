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
            'attendance_id'                  => 'required|exists:attendances,id',
            'clock_in'                       => 'nullable|date_format:H:i,G:i',
            'clock_out'                      => 'nullable|date_format:H:i,G:i',
            'breaks_data'                    => 'nullable|array',
            'breaks_data.*.break_start'      => 'nullable|date_format:H:i,G:i',
            'breaks_data.*.break_end'        => 'nullable|date_format:H:i,G:i',
            'note'                           => 'required|string|max:500',
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
            'note.required'                          => '備考を記入してください',
            'clock_in.date_format'                   => '出勤時間が不適切な値です',
            'clock_out.date_format'                  => '出勤時間が不適切な値です',
            'clock_out.after'                        => '出勤時間が不適切な値です',
            'breaks_data.*.break_start.date_format'  => '休憩時間が不適切な値です',
            'breaks_data.*.break_end.date_format'    => '休憩時間もしくは退勤時間が不適切な値です',
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
            'attendance_id'                  => '勤怠ID',
            'clock_in'                       => '出勤時間',
            'clock_out'                      => '退勤時間',
            'breaks_data.*.break_start'      => '休憩開始時間',
            'breaks_data.*.break_end'        => '休憩終了時間',
            'note'                           => '備考',
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

            // 出勤・退勤時間の順序チェック（最優先）
            if ($this->clock_in && $this->clock_out) {
                try {
                    $clockIn  = \Carbon\Carbon::createFromFormat('H:i', $this->clock_in);
                    $clockOut = \Carbon\Carbon::createFromFormat('H:i', $this->clock_out);

                    if ($clockOut->lte($clockIn)) {
                        $validator->errors()->add(
                            'clock_out',
                            '出勤時間もしくは退勤時間が不適切な値です'
                        );
                        return; // 早期リターン
                    }
                } catch (\Exception $e) {
                    return;
                }
            }

           // 休憩時間の整合性チェック
if ($this->breaks_data && $this->clock_in && $this->clock_out) {
    try {
        $clockIn = \Carbon\Carbon::createFromFormat('H:i', $this->clock_in);
        $clockOut = \Carbon\Carbon::createFromFormat('H:i', $this->clock_out);
    } catch (\Exception $e) {
        return;
    }
    
    foreach ($this->breaks_data as $index => $break) {
        // 空の休憩データはスキップ
        if (empty($break['break_start']) && empty($break['break_end'])) {
            continue;
        }
        
        try {
            // 1. 休憩開始と終了の順序チェック
            if (!empty($break['break_start']) && !empty($break['break_end'])) {
                $breakStart = \Carbon\Carbon::createFromFormat('H:i', $break['break_start']);
                $breakEnd = \Carbon\Carbon::createFromFormat('H:i', $break['break_end']);
                
                if ($breakEnd->lte($breakStart)) {
                    $validator->errors()->add('breaks_data.'.$index.'.break_end', '休憩時間が不適切な値です');
                    continue;
                }
            }
            
            // 2. 休憩開始時間が出勤〜退勤の範囲内かチェック
            if (!empty($break['break_start'])) {
                $breakStart = \Carbon\Carbon::createFromFormat('H:i', $break['break_start']);
                
                if ($breakStart->lt($clockIn) || $breakStart->gt($clockOut)) {
                    $validator->errors()->add('breaks_data.'.$index.'.break_start', '休憩時間が不適切な値です');
                    continue;
                }
            }
            
            // 3. 休憩終了時間が退勤時間より前かチェック
            if (!empty($break['break_end'])) {
                $breakEnd = \Carbon\Carbon::createFromFormat('H:i', $break['break_end']);
                
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
}