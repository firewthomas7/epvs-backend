<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_configs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('merchant_id');
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->string('bank_name', 100);
            $table->string('bank_code', 20);
            $table->string('secret_key_hash', 255);
            $table->json('allowed_ips')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_ping_at')->nullable();
            $table->unsignedInteger('total_calls')->default(0);
            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants');
            $table->index(['merchant_id', 'bank_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_configs');
    }
};
