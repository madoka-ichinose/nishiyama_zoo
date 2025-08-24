<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();

            // 投稿者
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // どの動物の写真か
            $table->foreignId('animal_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // （任意）どのコンテスト応募か。未応募なら null
            $table->foreignId('contest_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->string('image_path', 2048); // 保存先（public ディスク推奨）
            $table->text('comment')->nullable();

            // 承認フラグと承認メタ
            $table->boolean('is_approved')->default(false)->index();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable() // 承認した管理者（users.id）
                  ->constrained('users')
                  ->nullOnDelete();

            // 集計キャッシュ
            $table->unsignedInteger('likes_count')->default(0)->index();

            $table->timestamps();
            $table->softDeletes(); // 不適切対応や取り消しに備え

            // よく使う並び替え用インデックス
            $table->index(['animal_id', 'is_approved', 'created_at']);
            $table->index(['contest_id', 'is_approved', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
