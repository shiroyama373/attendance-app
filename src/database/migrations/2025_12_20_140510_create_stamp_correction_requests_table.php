<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();

            // ユーザー
            $table->foreignId('user_id')
                  ->constrained('users');

            // 出勤レコード
            $table->foreignId('attendance_id')
                  ->constrained('attendances');

            // 修正対象の打刻
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();

            // 休憩データ
            $table->json('breaks_data')->nullable();

            // メモ
            $table->text('note');

            // 承認ステータス
            $table->enum('status', ['pending','approved','rejected'])
                  ->default('pending');

            // 承認者（nullable、削除時 SET NULL）
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // 承認日時
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
};