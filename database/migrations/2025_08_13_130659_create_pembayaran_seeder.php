<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * OUTLET
         */
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('nama_outlet')->unique();
            $table->boolean('status')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * DETAIL USER OUTLET (siapa yang kelola outlet)
         */
        Schema::create('detail_user_outlet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->boolean('status')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'outlet_id']); // 1 user bisa di banyak outlet, tapi unik per outlet
        });

        /**
         * SALDO SANTRI
         */
        Schema::create('saldo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->unique()->constrained('santri')->cascadeOnDelete();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->boolean('status')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * KATEGORI
         */
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori')->unique();
            $table->boolean('status')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * OUTLET - KATEGORI (pivot)
         */
        Schema::create('outlet_kategori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(['outlet_id', 'kategori_id']);
        });

        /**
         * TRANSAKSI
         */
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $table->foreignId('user_outlet_id')->constrained('detail_user_outlet')->cascadeOnDelete(); // siapa + outlet
            $table->decimal('total_bayar', 15, 2);
            $table->dateTime('tanggal');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bank', 10)->unique();   // ex: BNI, BSI, MANDIRI
            $table->string('nama_bank', 100);            // ex: Bank Negara Indonesia, Bank Syariah Indonesia
            $table->boolean('status')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * VIRTUAL ACCOUNT
         */
        Schema::create('virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained('banks')->restrictOnDelete();
            $table->string('va_number', 30)->unique();
            $table->boolean('status')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tagihan', 50)->unique();
            $table->string('nama_tagihan', 150);
            $table->decimal('nominal', 15, 2);
            $table->date('jatuh_tempo')->nullable();
            $table->boolean('status')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tagihan_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihan')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();

            $table->decimal('nominal', 15, 2);
            $table->decimal('sisa', 15, 2)->default(0);
            $table->enum('status', ['pending', 'lunas', 'sebagian'])->default('pending');

            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->dateTime('tanggal_bayar')->nullable();
            $table->string('keterangan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tagihan_id', 'santri_id']);
            $table->index('status');
            $table->index('santri_id');
        });

        /**
         * PEMBAYARAN
         */
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_santri_id')->constrained('tagihan_santri')->cascadeOnDelete();
            $table->foreignId('virtual_account_id')->nullable()->constrained('virtual_accounts')->nullOnDelete();
            $table->enum('metode', ['VA', 'CASH', 'SALDO', 'TRANSFER']);
            $table->decimal('jumlah_bayar', 15, 2);
            $table->timestamp('tanggal_bayar')->useCurrent();
            $table->enum('status', ['berhasil', 'pending', 'gagal'])->default('pending');
            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * TRANSAKSI SALDO
         */
        Schema::create('transaksi_saldo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
            $table->foreignId('user_outlet_id')->nullable()->constrained('detail_user_outlet')->nullOnDelete();
            $table->enum('tipe', ['topup', 'debit', 'kredit', 'refund']);
            $table->decimal('jumlah', 15, 2);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        // /**
        //  * PAYMENT PROVIDERS (mis. midtrans, xendit, bank aggregator, dll)
        //  */
        // Schema::create('payment_providers', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('kode_provider', 50)->unique(); // ex: MIDTRANS, XENDIT, BANKAPI
        //     $table->string('nama_provider', 150);
        //     $table->json('config')->nullable(); // penyimpanan config/credential terenkripsi (endpoint, key, mode)
        //     $table->boolean('status')->default(true);

        //     $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        //     $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        //     $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

        //     $table->timestamps();
        //     $table->softDeletes();
        // });

        // /**
        //  * PROVIDER ACCOUNTS (akun/virtual account yang dikelola di tiap provider)
        //  * Berguna jika ada beberapa VA/merchant account di satu provider
        //  */
        // Schema::create('provider_accounts', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('provider_id')->constrained('payment_providers')->cascadeOnDelete();
        //     $table->string('kode_account', 100)->nullable(); // kode internal atau merchant id
        //     $table->string('nama_account', 150)->nullable();
        //     $table->string('bank_code', 20)->nullable(); // jika akun ini terkait bank tertentu (BNI, MANDIRI)
        //     $table->json('credentials')->nullable(); // credential khusus akun
        //     $table->boolean('status')->default(true);

        //     $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        //     $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        //     $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

        //     $table->timestamps();
        //     $table->softDeletes();
        //     $table->unique(['provider_id', 'kode_account']);
        // });

        // /**
        //  * TOPUP REQUESTS
        //  * Record permintaan topup dari frontend/pos. Provider akan memberi VA / payment_url.
        //  */
        // Schema::create('topup_requests', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('kode_topup', 50)->unique(); // kode internal, ex: TOPUP-20250817-0001
        //     $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
        //     $table->foreignId('provider_id')->nullable()->constrained('payment_providers')->nullOnDelete();
        //     $table->foreignId('provider_account_id')->nullable()->constrained('provider_accounts')->nullOnDelete();

        //     $table->decimal('jumlah_request', 15, 2);
        //     $table->decimal('fee', 15, 2)->default(0); // biaya admin/pembayaran
        //     $table->decimal('gross_amount', 15, 2)->nullable(); // jumlah yang harus dibayar ke provider (jumlah + fee)
        //     $table->decimal('net_amount', 15, 2)->nullable(); // jumlah bersih yang masuk ke saldo setelah fee

        //     $table->enum('metode', ['VA', 'TRANSFER', 'QR', 'CARD', 'CASH'])->default('VA');
        //     $table->string('bank_code', 20)->nullable(); // opsional: bank target
        //     $table->string('va_number', 50)->nullable()->unique(); // VA number dari provider (jika ada)
        //     $table->string('payment_url')->nullable(); // url pembayaran (jika ada)
        //     $table->timestamp('expired_at')->nullable();

        //     $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'cancelled'])->default('pending');

        //     $table->json('provider_response')->nullable(); // raw response saat create VA/payment
        //     $table->json('callback_payload')->nullable(); // payload terakhir diterima dari provider
        //     $table->timestamp('paid_at')->nullable();

        //     $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        //     $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        //     $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

        //     $table->timestamps();
        //     $table->softDeletes();
        // });

        // /**
        //  * TOPUP TRANSACTIONS
        //  * Transaksi akuntansi / rekonsiliasi untuk tiap notifikasi pembayaran dari provider.
        //  * Bisa digabungkan langsung dengan transaksi_saldo, tapi disarankan pisah untuk audit.
        //  */
        // Schema::create('topup_transactions', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('topup_request_id')->constrained('topup_requests')->cascadeOnDelete();
        //     $table->string('provider_reference')->nullable()->unique(); // id/transaksi dari provider (ex: payment_id)
        //     $table->decimal('jumlah', 15, 2);
        //     $table->decimal('fee', 15, 2)->default(0);
        //     $table->decimal('net_amount', 15, 2)->nullable(); // jumlah yang akan dikreditkan ke saldo
        //     $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
        //     $table->timestamp('tanggal_diterima')->nullable(); // waktu notif dari provider
        //     $table->json('payload')->nullable(); // raw webhook payload

        //     // optional link to transaksi_saldo (create entry pada reconciling)
        //     $table->foreignId('transaksi_saldo_id')->nullable()->constrained('transaksi_saldo')->nullOnDelete();

        //     $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        //     $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        //     $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

        //     $table->timestamps();
        //     $table->softDeletes();
        // });

        // /**
        //  * BANK/PROVIDER WEBHOOKS (log semua callback masuk untuk audit & retry)
        //  */
        // Schema::create('provider_webhooks', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('provider_id')->nullable()->constrained('payment_providers')->nullOnDelete();
        //     $table->string('event')->nullable();
        //     $table->string('reference')->nullable(); // provider reference
        //     $table->json('payload')->nullable();
        //     $table->boolean('processed')->default(false);
        //     $table->text('error')->nullable();
        //     $table->timestamp('received_at')->useCurrent();
        //     $table->timestamp('processed_at')->nullable();

        //     $table->timestamps();
        // });

        // /**
        //  * SETTLEMENT / RECONCILIATION BATCH (opsional)
        //  */
        // Schema::create('provider_settlements', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('provider_id')->nullable()->constrained('payment_providers')->nullOnDelete();
        //     $table->date('tanggal_settlement')->nullable();
        //     $table->integer('jumlah_transaksi')->default(0);
        //     $table->decimal('total_gross', 18, 2)->default(0);
        //     $table->decimal('total_fee', 18, 2)->default(0);
        //     $table->decimal('total_net', 18, 2)->default(0);
        //     $table->enum('status', ['unreconciled', 'reconciled'])->default('unreconciled');
        //     $table->json('detail')->nullable(); // file/snippet settlement

        //     $table->timestamps();
        // });
    }

    public function down(): void
    {
        // Schema::dropIfExists('provider_settlements');
        // Schema::dropIfExists('provider_webhooks');
        // Schema::dropIfExists('topup_transactions');
        // Schema::dropIfExists('topup_requests');
        // Schema::dropIfExists('provider_accounts');
        // Schema::dropIfExists('payment_providers');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('outlet_kategori');
        Schema::dropIfExists('kategori');
        Schema::dropIfExists('saldo');
        Schema::dropIfExists('detail_user_outlet');
        Schema::dropIfExists('outlet');
        Schema::dropIfExists('transaksi_saldo');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('tagihan');
        Schema::dropIfExists('santri_virtual_accounts');
        Schema::dropIfExists('virtual_accounts');
    }
};
