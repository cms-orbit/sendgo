<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sendgo_delivery_logs')) {
            return;
        }

        Schema::create('sendgo_delivery_logs', function (Blueprint $table): void {
            $table->id();
            $table->char('store_uuid', 36)->nullable()->charset('ascii')->index();
            $table->string('template_code')->nullable();
            $table->string('channel', 32)->default('alimtalk');
            $table->unsignedInteger('recipient_count')->default(1);
            $table->boolean('success')->default(true);
            $table->timestamps();

            $table->index(['store_uuid', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendgo_delivery_logs');
    }
};
