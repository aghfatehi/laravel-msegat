<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('msegat_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('bulk_id')->nullable()->index();
            $table->string('sender');
            $table->text('message');
            $table->string('recipients')->comment('Comma-separated numbers');
            $table->string('status')->default('pending')->index();
            $table->string('response_code')->nullable();
            $table->text('response_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msegat_sms_logs');
    }
};
