<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sendgo_templates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('template_code')->index();
            $table->string('template_name')->nullable();
            $table->string('status')->nullable();
            $table->string('inspection_status')->nullable();
            $table->string('kakao_sender_id')->nullable()->index();
            $table->text('template_content')->nullable();
            $table->json('buttons')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sendgo_templates');
    }
};
