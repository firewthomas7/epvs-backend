<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('epvs_id', 20)->unique();
            $table->string('business_name');
            $table->string('business_type', 100)->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('tin_number', 50)->nullable();
            $table->string('business_license', 100)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->enum('verification_status', ['pending', 'under_review', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('logo_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('owner_id')->references('id')->on('users');
            $table->index('epvs_id');
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
