<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('epvs_ref', 30)->unique();
            $table->unsignedBigInteger('merchant_id');
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->enum('source_type', ['bank_api', 'telebirr', 'sms', 'ussd', 'manual'])->default('bank_api');
            $table->enum('status', ['pending', 'verified', 'failed', 'suspicious', 'duplicate'])->default('pending');
            $table->decimal('amount', 18, 2);
            $table->string('currency', 10)->default('ETB');
            $table->string('sender_name', 200)->nullable();
            $table->string('sender_phone', 20)->nullable();
            $table->string('sender_account', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_code', 20)->nullable();
            $table->string('bank_reference', 100)->nullable();
            $table->text('description')->nullable();
            $table->json('raw_payload')->nullable();
            $table->tinyInteger('ai_confidence_score')->nullable();
            $table->timestamp('bank_timestamp')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->boolean('is_announced')->default(false);
            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts');
            $table->foreign('verified_by')->references('id')->on('users');
            $table->unique(['bank_account_id', 'bank_reference'], 'unique_bank_ref');
            $table->index(['merchant_id', 'status', 'created_at']);
            $table->index(['merchant_id', 'created_at']);
            $table->index('epvs_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
