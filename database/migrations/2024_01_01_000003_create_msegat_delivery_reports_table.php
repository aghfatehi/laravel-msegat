<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('msegat_delivery_reports', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->index();
            $table->string('bulk_id')->nullable()->index();
            $table->string('number');
            $table->string('status')->index();
            $table->text('failure_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msegat_delivery_reports');
    }
};
