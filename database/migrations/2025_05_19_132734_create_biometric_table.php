<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_name', 100);
            $table->string('location', 191)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->enum('type', ['fingerprint', 'card', 'both']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('biometric_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('santri_id');
            $table->string('card_uid', 100)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
            $table->unique(['santri_id']);
        });

        Schema::create('biometric_fingerprints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('biometric_profile_id');
            $table->enum('finger_position', [
                'right_thumb',
                'right_index',
                'right_middle',
                'right_ring',
                'right_little',
                'left_thumb',
                'left_index',
                'left_middle',
                'left_ring',
                'left_little',
            ]);
            $table->text('template');
            $table->unsignedTinyInteger('scan_order')->default(1); // urutan scan ke berapa, default 1
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('biometric_profile_id')->references('id')->on('biometric_profiles')->onDelete('cascade');
            $table->unique(['biometric_profile_id', 'finger_position', 'scan_order'], 'uniq_finger_per_profile_scan');
        });

        Schema::create('biometric_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('biometric_profile_id')->nullable();
            $table->uuid('device_id')->nullable();
            $table->enum('method', ['fingerprint', 'card']);
            $table->timestamp('scanned_at');
            $table->boolean('success')->default(true);
            $table->string('message', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('biometric_profile_id')->references('id')->on('biometric_profiles')->onDelete('set null');
            $table->foreign('device_id')->references('id')->on('biometric_devices')->onDelete('set null');
            $table->index(['biometric_profile_id', 'device_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_logs');
        Schema::dropIfExists('biometric_fingerprints');
        Schema::dropIfExists('biometric_profiles');
        Schema::dropIfExists('biometric_devices');
    }
};
