<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2. Tabel metode_pembayaran
        Schema::create('metode_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 3. Tabel jenis_pembayaran
        Schema::create('jenis_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('berulang')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 4. Tabel rencana_pembayaran
        Schema::create('rencana_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_pembayaran_id')->constrained('jenis_pembayaran');
            $table->decimal('nominal', 12, 2);
            $table->tinyInteger('bulan')->nullable();
            $table->smallInteger('tahun');
            $table->date('jatuh_tempo')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 5. Tabel tagihan_santri
        Schema::create('tagihan_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri');
            $table->foreignId('rencana_pembayaran_id')->constrained('rencana_pembayaran');
            $table->decimal('total_tagihan', 12, 2);
            $table->decimal('total_dibayar', 12, 2)->default(0.00);
            $table->enum('status', ['belum_lunas', 'sebagian', 'lunas'])->default('belum_lunas');
            $table->date('jatuh_tempo')->nullable();
            $table->timestamps();
        });

        // 6. Tabel pembayaran_santri
        Schema::create('pembayaran_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_santri_id')->constrained('tagihan_santri');
            $table->date('tanggal_pembayaran');
            $table->decimal('nominal', 12, 2);
            $table->foreignId('metode_pembayaran_id')->nullable()->constrained('metode_pembayaran');
            $table->string('kode_referensi', 100)->nullable(); // No kwitansi / bukti transfer
            $table->string('diterima_oleh', 100)->nullable();
            $table->boolean('dibatalkan')->default(false);
            $table->timestamp('tanggal_dibatalkan')->nullable();
            $table->timestamps();
        });

        // 7. Tabel subsidi_santri (opsional)
        Schema::create('subsidi_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_santri_id')->constrained('tagihan_santri');
            $table->decimal('nominal', 12, 2);
            $table->text('alasan')->nullable();
            $table->string('diberikan_oleh', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subsidi_santri');
        Schema::dropIfExists('pembayaran_santri');
        Schema::dropIfExists('tagihan_santri');
        Schema::dropIfExists('rencana_pembayaran');
        Schema::dropIfExists('jenis_pembayaran');
        Schema::dropIfExists('metode_pembayaran');
        Schema::dropIfExists('santri');
    }
};
