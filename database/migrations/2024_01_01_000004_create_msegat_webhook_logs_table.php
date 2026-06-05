<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('msegat_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('signature')->nullable();
            $table->string('status')->default('received')->index();
            $table->json('payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msegat_webhook_logs');
    }
};
