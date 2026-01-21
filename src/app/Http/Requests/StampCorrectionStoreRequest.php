<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StampCorrectionStoreRequest extends FormRequest
{
    /**
     * 認可
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'attendance_id'             => 'required|exists:attendances,id',
            'clock_in'                  => 'nullable|date_format:H:i',
            'clock_out'                 => 'nullable|date_format:H:i',
            'breaks_data'               => 'nullable|array',
            'breaks_data.*.break_start' => 'nullable|date_format:H:i',
            'breaks_data.*.break_end'   => 'nullable|date_format:H:i',
            'note'                      => 'required|string|max:500',
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'note.required'                         => '備考を記入してください',
            'clock_in.date_format'                  => '出勤時間が不適切な値です',
            'clock_out.date_format'                 => '出勤時間が不適切な値です',
            'breaks_data.*.break_start.date_format' => '休憩時間が不適切な値です',
            'breaks_data.*.break_end.date_format'   => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }

    /**
     * 属性名
     */
    public function attributes(): array
    {
        return [
            'attendance_id'             => '勤怠ID',
            'clock_in'                  => '出勤時間',
            'clock_out'                 => '退勤時間',
            'breaks_data.*.break_start' => '休憩開始時間',
            'breaks_data.*.break_end'   => '休憩終了時間',
            'note'                      => '備考',
        ];
    }

    /**
     * 追加バリデーション
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $clockIn = null;
            $clockOut = null;

            /*
             |--------------------------------------------------------------------------
             | 出勤・退勤時間の整合性チェック
             |--------------------------------------------------------------------------
             */
            if ($this->clock_in && $this->clock_out) {
                try {
                    $clockIn  = Carbon::createFromFormat('H:i', $this->clock_in);
                    $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

                    if ($clockOut->lte($clockIn)) {
                        $validator->errors()->add(
                            'clock_out',
                            '出勤時間もしくは退勤時間が不適切な値です'
                        );
                    }
                } catch (\Exception $e) {
                    // 日付変換失敗はルールの date_format で対応
                }
            }

            /*
             |--------------------------------------------------------------------------
             | 休憩時間の整合性チェック
             |--------------------------------------------------------------------------
             */
            if ($this->breaks_data && $clockIn && $clockOut) {
                foreach ($this->breaks_data as $index => $break) {

                    // 空行は無視
                    if (empty($break['break_start']) && empty($break['break_end'])) {
                        continue;
                    }

                    try {
                        // 休憩開始・終了の Carbon 変換
                        $breakStart = !empty($break['break_start']) ? Carbon::createFromFormat('H:i', $break['break_start']) : null;
                        $breakEnd   = !empty($break['break_end']) ? Carbon::createFromFormat('H:i', $break['break_end']) : null;

                        // ① 休憩開始 < 休憩終了
                        if ($breakStart && $breakEnd && $breakEnd->lte($breakStart)) {
                            $validator->errors()->add(
                                "breaks_data.{$index}.break_end",
                                '休憩時間が不適切な値です'
                            );
                        }

                        // ② 休憩開始が勤務時間内か
                        if ($breakStart && ($breakStart->lt($clockIn) || $breakStart->gt($clockOut))) {
                            $validator->errors()->add(
                                "breaks_data.{$index}.break_start",
                                '休憩時間が不適切な値です'
                            );
                        }

                        // ③ 休憩終了が退勤後でないか
                        if ($breakEnd && $breakEnd->gt($clockOut)) {
                            $validator->errors()->add(
                                "breaks_data.{$index}.break_end",
                                '休憩時間もしくは退勤時間が不適切な値です'
                            );
                        }
                    } catch (\Exception $e) {
                        continue; // 無効な時刻は date_format で弾かれる
                    }
                }
            }
        });
    }
}