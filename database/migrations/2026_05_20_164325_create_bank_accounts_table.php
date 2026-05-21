<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('merchant_id');
            $table->string('bank_name', 100);
            $table->string('bank_code', 20);
            $table->text('account_number_encrypted');
            $table->string('account_number_masked', 30);
            $table->string('account_name', 150);
            $table->enum('account_type', ['savings', 'current', 'business'])->default('current');
            $table->string('webhook_token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->index(['merchant_id', 'is_active']);
            $table->index('webhook_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
