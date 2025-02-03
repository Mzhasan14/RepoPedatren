<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('biodata', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->softDeletes();
        });
        Schema::table('peserta_didik', function (Blueprint $table) {
            $table->dropColumn('delete_at');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodata', function (Blueprint $table) {
            $table->timestamp('deleted_at');
            $table->dropSoftDeletes();
        });
        Schema::table('peserta_didik', function (Blueprint $table) {
            $table->timestamp('deleted_at');
            $table->dropSoftDeletes();
        });
    }
};
