<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();              // 例：レッサーパンダ
            $table->text('description')->nullable();
            $table->string('habitat')->nullable();        // 生息地
            $table->string('favorite_food')->nullable();  // 好きな食べ物
            $table->text('comment')->nullable();
            $table->string('image_path', 2048)->nullable(); // 紹介用画像のパス（/storage/...）
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
