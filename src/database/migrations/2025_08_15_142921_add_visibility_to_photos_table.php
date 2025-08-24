<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibilityToPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->after('is_approved');
            $table->timestamp('hidden_at')->nullable()->after('is_visible');
            $table->string('hidden_reason', 255)->nullable()->after('hidden_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['is_visible','hidden_at','hidden_reason']);
        });
    }
}
