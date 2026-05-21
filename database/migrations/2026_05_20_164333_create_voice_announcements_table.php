<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voice_announcements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('merchant_id');
            $table->unsignedBigInteger('transaction_id');
            $table->text('script');
            $table->string('language', 10)->default('am');
            $table->string('audio_url')->nullable();
            $table->enum('status', ['queued', 'generating', 'ready', 'played', 'failed'])->default('queued');
            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants');
            $table->foreign('transaction_id')->references('id')->on('transactions');
            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_announcements');
    }
};
