<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('merchant_id')->nullable();
            $table->string('from_number', 30);
            $table->string('to_number', 30)->nullable();
            $table->text('body');
            $table->timestamp('received_at');
            $table->boolean('parsed')->default(false);
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->enum('parsing_status', ['pending', 'processing', 'success', 'failed', 'ignored'])->default('pending');
            $table->string('parsing_error')->nullable();
            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants');
            $table->foreign('transaction_id')->references('id')->on('transactions');
            $table->index(['merchant_id', 'parsing_status']);
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
