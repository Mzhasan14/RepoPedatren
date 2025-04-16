<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\Auth\AuthController;
use App\Http\Controllers\Api\{
    BiodataController,
    PerizinanController,
    PelanggaranController,
    JenisBerkasController,
    BerkasController,
    CatatanAfektifController,
    CatatanKognitifController,
    KhadamController,
};

use App\Http\Controllers\Api\PesertaDidik\{
    PesertaDidikController,
    PelajarController,
    SantriController,
    AlumniController,
};

use App\Http\Controllers\Api\keluarga\{
    KeluargaController,
    StatusKeluargaController,
    OrangTuaWaliController
};

use App\Http\Controllers\Api\Alamat\{
    ProvinsiController,
    KabupatenController,
    KecamatanController
};
use App\Http\Controllers\api\formulir\PesertaDidikFormulir;
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
    AnakPegawaiController,
    PegawaiController,
    PengajarController,
    WalikelasController,
    KategoriGolonganController,
    GolonganController,
    EntitasController,
    PengurusController,
    KaryawanController,
    MateriAjarController,
    DropdownController
};

// Route untuk autentikasi
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Endpoint untuk registrasi dan login
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Endpoint logout hanya bisa diakses oleh pengguna yang sudah terautentikasi
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Untuk Data Pokok Nanti
Route::prefix('data-pokok')->middleware(['auth:sanctum', 'role:superadmin|admin|supervisor'])->group(function () {
   // Route CRUD
   Route::middleware('auth:sanctum', 'role:superadmin|admin')->group(function () {
    // CRUD Pelajar
    Route::post('/pelajar', [PelajarController::class, 'store']);
    Route::put('/pelajar/{id}', [PelajarController::class, 'update']);
    Route::delete('/pelajar/{id}', [PelajarController::class, 'destroy']);
   });
});

Route::prefix('formulir')->group(function () {
    Route::get('/{id}/biodata', [PesertaDidikFormulir::class, 'getBiodata']);
    Route::get('/{id}/keluarga', [PesertaDidikFormulir::class, 'getKeluarga']);
    Route::get('/{id}/santri', [PesertaDidikFormulir::class, 'getSantri']);
    Route::get('/{id}/domisili', [PesertaDidikFormulir::class, 'getDomisiliSantri']);
    Route::get('/{id}/pendidikan', [PesertaDidikFormulir::class, 'getPendidikan']);
    Route::get('/{id}/berkas', [PesertaDidikFormulir::class, 'getBerkas']);
    Route::get('/{id}/wargapesantren', [PesertaDidikFormulir::class, 'getWargaPesantren']);
});

// Grouping API
Route::prefix('data-pokok')->group(function () {

    //Biodata
    Route::apiResource('/biodata', BiodataController::class);

    // 🏫 Santri & Peserta Didik
    Route::apiResource('/crud/peserta_didik', PesertaDidikController::class);
    // Route::post('/pelajar', [PelajarController::class, 'store']);
    Route::apiResource('/crud/santri', SantriController::class);
    Route::get('/pesertadidik', [PesertaDidikController::class, 'getAllPesertaDidik']);
    Route::get('/pesertadidik-bersaudara', [PesertaDidikController::class, 'getAllBersaudara']);
    Route::get('/pesertadidik-bersaudara/{id}', [PesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/pesertadidik/{id}', [PesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/santri', [SantriController::class, 'getAllSantri']);
    Route::get('/santri/{id}', [SantriController::class, 'getDetailSantri']);
    Route::get('/pelajar', [PelajarController::class, 'getAllPelajar']);
    Route::get('/pelajar/{id}', [PelajarController::class, 'getDetailPelajar']);
    Route::get('/alumni', [AlumniController::class, 'alumni']);
    Route::get('/alumni/{id}', [AlumniController::class, 'getDetailAlumni']);
    Route::apiResource('/catatan-afektif', CatatanAfektifController::class);
    Route::apiResource('/catatan-kognitif', CatatanKognitifController::class);

    // 🏫 Keluarga
    Route::apiResource('/crud/keluarga', KeluargaController::class);
    Route::get('/keluarga', [KeluargaController::class, 'keluarga']);
    Route::apiResource('/crud/status-keluarga', StatusKeluargaController::class);
    Route::apiResource('/crud/orangtua', OrangTuaWaliController::class);
    Route::get('/orangtua', [OrangTuaWaliController::class, 'orangTuaWali']);
    Route::get('/wali', [OrangTuaWaliController::class, 'wali']);

    // 📍 Alamat
    Route::apiResource('/provinsi', ProvinsiController::class);
    Route::apiResource('/kabupaten', KabupatenController::class);
    Route::apiResource('/kecamatan', KecamatanController::class);

    // 🏠 Kewaliasuhan (Asrama/Pengasuhan)
    Route::apiResource('/grup-waliasuh', GrupWaliAsuhController::class);
    Route::apiResource('/waliasuh', WaliasuhController::class);
    Route::apiResource('/anakasuh', AnakasuhController::class);
    Route::get('/list/waliasuh', [WaliasuhController::class, 'waliAsuh']);
    Route::get('/list/anakasuh', [AnakasuhController::class, 'anakAsuh']);
    Route::get('/list/kewaliasuhan', [GrupWaliAsuhController::class, 'kewaliasuhan']);

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
    Route::apiResource('/anakpegawai', AnakPegawaiController::class);
    Route::apiResource('/materiAjar', MateriAjarController::class);
    Route::get('/berkas', [BerkasController::class, 'Berkas']);
    Route::get('/pengajars', [PengajarController::class, 'filterPengajar']);
    Route::get('/pengurus', [PengurusController::class, 'dataPengurus']);
    Route::get('/walikelas', [WalikelasController::class, 'dataWalikelas']);
    Route::get('/karyawans', [KaryawanController::class, 'dataKaryawan']);
    Route::get('/pegawais', [PegawaiController::class, 'dataPegawai']);
    Route::get('/anakpegawais', [AnakPegawaiController::class, 'getAllAnakpegawai']);

    // 🚨 Administrasi
    Route::apiResource('/perizinan', PerizinanController::class);
    Route::apiResource('/pelanggaran', PelanggaranController::class);

    // Khadam
    Route::get('/khadam', [KhadamController::class, 'getAllKhadam']);
    Route::get('/khadam/{id}', [KhadamController::class, 'getDetailKhadam']);
});
Route::prefix('detail')->group(function () {
    Route::get('/pengurus/{id}', [PengurusController::class, 'getPengurus']);
    Route::get('/pengajar/{id}', [PengajarController::class, 'getPengajar']);
    Route::get('/karyawan/{id}', [KaryawanController::class, 'getKaryawan']);
    Route::get('pegawai/{id}', [PegawaiController::class, 'getPegawai']);
    Route::get('/anakpegawai/{id}', [AnakPegawaiController::class, 'getAnakPegawai']);
    Route::get('/walikelas/{id}', [WalikelasController::class, 'getWalikelas']);
});
Route::prefix('dropdown')->group(function () {
    Route::get('/catatan-afektif', [CatatanAfektifController::class, 'dataCatatanAfektif']);
    Route::get('/catatan-kognitif', [CatatanKognitifController::class, 'dataCatatanKognitif']);
    Route::get('/wilayah', [DropdownController::class, 'menuWilayahBlokKamar']);
    Route::get('/negara', [DropdownController::class, 'menuNegaraProvinsiKabupatenKecamatan']);
    Route::get('/lembaga', [DropdownController::class, 'menuLembagaJurusanKelasRombel']);
    Route::get('/angkatan', [DropdownController::class, 'getAngkatan']);
    Route::get('/periode', [DropdownController::class, 'getPeriodeOptions']);
    Route::get('/golongan', [DropdownController::class, 'menuKategoriGolonganAndGolongan']);
    Route::get('/materi-ajar', [DropdownController::class, 'menuMateriAjar']);
});
