<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    BiodataController,
    PerizinanController,
    PelanggaranController,
    PesertaDidikController,
    JenisBerkasController,
    BerkasController,
    CatatanAfektifController,
    CatatanKognitifController
};

use App\Http\Controllers\Api\keluarga\{
    KeluargaController,
    StatusKeluargaController,
    OrangTuaController
};

use App\Http\Controllers\Api\Alamat\{
    ProvinsiController,
    KabupatenController,
    KecamatanController,
    DesaController
};
use App\Http\Controllers\Api\Kewaliasuhan\{
    GrupWaliAsuhController,
    WaliasuhController,
    AnakasuhController
};
use App\Http\Controllers\Api\Wilayah\{
    WilayahController,
    BlokController,
    KamarController,
    DomisiliController
};
use App\Http\Controllers\Api\Pendidikan\{
    LembagaController,
    JurusanController,
    KelasController,
    RombelController
};
use App\Http\Controllers\Api\Pegawai\{
    PegawaiController,
    PengajarController,
    WalikelasController,
    KategoriGolonganController,
    GolonganController,
    EntitasController,
    PengurusController,
    KaryawanController,
};
use App\Models\Peserta_didik;

// Route untuk autentikasi
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Grouping API
Route::prefix('v1')->group(function () {

    //Biodata
    Route::get('{id}/warga-pesantren',[BiodataController::class, 'WargaPesantren']);
    Route::apiResource('/biodata', BiodataController::class);

    // 🏫 Santri & Peserta Didik
    Route::apiResource('/crud-peserta-didik', PesertaDidikController::class);
    Route::get('/pesertaDidik', [PesertaDidikController::class, 'pesertaDidik']);
    Route::get('/pesertaDidik/santri', [PesertaDidikController::class, 'santri']);
    Route::apiResource('/catatan-afektif',CatatanAfektifController::class);
    Route::apiResource('/catatan-kognitif',CatatanKognitifController::class);
    Route::get('/list-alumni',[PesertaDidikController::class,'alumni']);
    
    // 🏫 Keluarga
    Route::apiResource('/keluarga', KeluargaController::class);
    Route::get('/list/keluarga',[KeluargaController::class,'keluarga']);
    Route::apiResource('/status-keluarga', StatusKeluargaController::class);
    Route::apiResource('/orangtua', OrangTuaController::class);
    Route::get('/list/orangtua',[OrangTuaController::class,'getOrtu']);
    Route::get('/list/datawali', [KeluargaController::class, 'dataWali']);

    // 📍 Alamat
    Route::apiResource('/provinsi', ProvinsiController::class);
    Route::apiResource('/kabupaten', KabupatenController::class);
    Route::apiResource('/kecamatan', KecamatanController::class);
    Route::apiResource('/desa', DesaController::class);

    // 🏠 Kewaliasuhan (Asrama/Pengasuhan)
    Route::apiResource('/grup-waliasuh', GrupWaliAsuhController::class);
    Route::apiResource('/waliasuh', WaliasuhController::class);
    Route::apiResource('/anakasuh', AnakasuhController::class);
    Route::get('/list-wali-asuh',[WaliasuhController::class,'waliAsuh']);
    Route::get('/list-anak-asuh', [AnakasuhController::class, 'anakAsuh']);
    Route::get('/list-kewaliasuhan', [GrupWaliAsuhController::class, 'kewaliasuhan']);

    // 🏠 Wilayah (Blok, Kamar, Domisili)
    Route::apiResource('/wilayah', WilayahController::class);
    Route::apiResource('/blok', BlokController::class);
    Route::apiResource('/kamar', KamarController::class);
    Route::apiResource('/domisili', DomisiliController::class);

    // 🎓 Pendidikan
    Route::apiResource('/lembaga', LembagaController::class);
    Route::apiResource('/jurusan', JurusanController::class);
    Route::apiResource('/kelas', KelasController::class);
    Route::apiResource('/rombel', RombelController::class);

    // 👨‍🏫 Pegawai & Guru
    Route::apiResource('/pegawai', PegawaiController::class);
    Route::apiResource('/pengajar', PengajarController::class);
    Route::apiResource('/walikelas', WalikelasController::class);
    Route::apiResource('/kategori-golongan', KategoriGolonganController::class);
    Route::apiResource('/golongan', GolonganController::class);
    Route::apiResource('/entitas', EntitasController::class);
    Route::apiResource('/pengurus', PengurusController::class);
    Route::apiResource('/karyawan', KaryawanController::class);
    Route::apiResource('/jenisberkas', JenisBerkasController::class);
    Route::get('/list-pengajar', [PengajarController::class, 'Pengajar']);
    Route::get('/berkas', [BerkasController::class, 'Berkas']);
    Route::get('/pengajar-filter', [PengajarController::class, 'filterPengajar']);
    Route::get('/list-pengurus',[PengurusController::class,'dataPengurus']);
    Route::get('/list-walikelas',[WalikelasController::class,'dataWalikelas']);

    // 🚨 Administrasi
    Route::apiResource('/perizinan', PerizinanController::class);
    Route::apiResource('/pelanggaran', PelanggaranController::class);
});
