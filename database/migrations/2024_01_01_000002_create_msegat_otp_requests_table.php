<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('msegat_otp_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number')->index();
            $table->string('otp_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('code_hash')->nullable()->comment('SHA-256 hash of OTP code');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(5);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msegat_otp_requests');
    }
};
