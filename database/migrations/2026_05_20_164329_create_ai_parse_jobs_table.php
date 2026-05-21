<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_parse_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('sms_log_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->text('input_text');
            $table->json('parsed_result')->nullable();
            $table->tinyInteger('confidence_score')->nullable();
            $table->string('model_used', 50)->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('sms_log_id')->references('id')->on('sms_logs');
            $table->foreign('transaction_id')->references('id')->on('transactions');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_parse_jobs');
    }
};
