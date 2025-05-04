<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\Auth\AuthController;
use App\Http\Controllers\Api\{
    JenisBerkasController,
    BerkasController,

};

use App\Http\Controllers\Api\Administrasi\{
    CatatanAfektifController as AdministrasiCatatanAfektifController,
    CatatanKognitifController,
    DetailPelanggaranController,
    PerizinanController,
    PelanggaranController,
    DetailPerizinanController,
    DetailPelanggaranService
};
use App\Http\Controllers\Api\PesertaDidik\{
    AnakPegawaiController,
    PesertaDidikController,
    PelajarController,
    SantriController,
    AlumniController,
    DetailPesertaDidikController,
    KhadamController,
};

use App\Http\Controllers\Api\keluarga\{
    DetailOrangtuaController,
    DetailWaliController,
    KeluargaController,
    StatusKeluargaController,
    OrangTuaWaliController,
    WaliController
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
    PegawaiController,
    PengajarController,
    WalikelasController,
    KategoriGolonganController,
    GolonganController,
    PengurusController,
    KaryawanController,
    MateriAjarController,
    DropdownController
};

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login'])->middleware('throttle:7,1')->name('login');
Route::post('forgot',   [AuthController::class, 'forgotPassword']);
Route::post('reset',    [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',  [AuthController::class, 'logout']);
    Route::patch('profile', [AuthController::class, 'updateProfile']);
    Route::post('password', [AuthController::class, 'changePassword']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});


// Untuk Data Pokok Nanti
// Route::prefix('data-pokok')->middleware(['auth:sanctum', 'role:superadmin|admin|supervisor'])->group(function () {
//    // Route CRUD
//    Route::middleware('auth:sanctum', 'role:superadmin|admin')->group(function () {
//     // CRUD Pelajar
//     Route::post('/pelajar', [PelajarController::class, 'store']);
//     Route::put('/pelajar/{id}', [PelajarController::class, 'update']);
//     Route::delete('/pelajar/{id}', [PelajarController::class, 'destroy']);
//    });
// });

Route::prefix('formulir')->group(function () {
    Route::get('/{id}/biodata', [PesertaDidikFormulir::class, 'getBiodata']);
    Route::get('/{id}/keluarga', [PesertaDidikFormulir::class, 'getKeluarga']);
    Route::get('/{id}/santri', [PesertaDidikFormulir::class, 'getSantri']);
    Route::get('/{id}/domisili', [PesertaDidikFormulir::class, 'getDomisiliSantri']);
    Route::get('/{id}/pendidikan', [PesertaDidikFormulir::class, 'getPendidikan']);
    Route::get('/{id}/berkas', [PesertaDidikFormulir::class, 'getBerkas']);
    Route::get('/{id}/wargapesantren', [PesertaDidikFormulir::class, 'getWargaPesantren']);
});

Route::prefix('export')->group(function () {
    Route::get('/pesertadidik', [PesertaDidikController::class, 'pesertaDidikExport'])->name('pesertadidik.export');
    Route::get('/santri', [SantriController::class, 'santriExport'])->name('santri.export');
    Route::get('/pelajar', [PelajarController::class, 'pelajarExport'])->name('pelajar.export');
    Route::get('/pesertadidik-bersaudara', [PelajarController::class, 'bersaudaraExport'])->name('bersaudara.export');
    Route::get('/khadam', [KhadamController::class, 'khadamExport'])->name('khadam.export');
});

Route::prefix('crud')->middleware('auth:sanctum')->group(function () {
    Route::post('/pesertadidik', [PesertaDidikController::class, 'store']);
    Route::put('/pesertadidik/{id}', [PesertaDidikController::class, 'update']);
    Route::delete('/pesertadidik/{id}', [PesertaDidikController::class, 'destroy']);
});

Route::prefix('data-pokok')->group(function () {

    // 🏫 Santri & Peserta Didik
    Route::get('/pesertadidik', [PesertaDidikController::class, 'getAllPesertaDidik']);
    Route::get('/pesertadidik-bersaudara', [PesertaDidikController::class, 'getAllBersaudara']);
    Route::get('/pesertadidik-bersaudara/{id}', [DetailPesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/pesertadidik/{id}', [DetailPesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/santri', [SantriController::class, 'getAllSantri']);
    Route::get('/santri-nondomisili', [SantriController::class, 'getNonDomisili']);
    Route::get('/santri-nondomisili/{id}', [DetailPesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/santri/{id}', [DetailPesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/pelajar', [PelajarController::class, 'getAllPelajar']);
    Route::get('/pelajar/{id}', [DetailPesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/alumni', [AlumniController::class, 'alumni']);
    Route::get('/alumni/{id}', [DetailPesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/anakpegawai', [AnakPegawaiController::class, 'getAllAnakpegawai']);

    // Khadam
    Route::get('/khadam', [KhadamController::class, 'getAllKhadam']);
    Route::get('/khadam/{id}', [KhadamController::class, 'getDetailKhadam']);

    // 🚨 Administrasi
    Route::get('/perizinan', [PerizinanController::class, 'getAllPerizinan']);
    Route::get('/perizinan/{id}', [DetailPerizinanController::class, 'getDetailPerizinan']);
    Route::get('/pelanggaran', [PelanggaranController::class, 'getAllPelanggaran']);
    Route::get('/pelanggaran/{id}', [DetailPelanggaranController::class, 'getDetailPelanggaran']);

    Route::get('/catatan-afektif', [AdministrasiCatatanAfektifController::class, 'getCatatanAfektif']);
    Route::get('/catatan-kognitif', [CatatanKognitifController::class, 'getCatatanKognitif']);

    // 🏫 Keluarga
    Route::apiResource('/crud/keluarga', KeluargaController::class);
    Route::get('/keluarga', [KeluargaController::class, 'keluarga']);
    Route::apiResource('/crud/status-keluarga', StatusKeluargaController::class);
    Route::apiResource('/crud/orangtua', OrangTuaWaliController::class);
    Route::get('/orangtua', [OrangTuaWaliController::class, 'getAllOrangtua']);
    Route::get('/orangtua/{id}', [OrangTuaWaliController::class, 'getDetailOrangtua']);
    Route::get('/wali', [WaliController::class, 'getAllWali']);
    Route::get('/wali/{id}', [WaliController::class, 'getDetailWali']);

    // 📍 Alamat
    Route::apiResource('/provinsi', ProvinsiController::class);
    Route::apiResource('/kabupaten', KabupatenController::class);
    Route::apiResource('/kecamatan', KecamatanController::class);

    // 🏠 Kewaliasuhan (Asrama/Pengasuhan)
    Route::apiResource('/grup-waliasuh', GrupWaliAsuhController::class);
    Route::apiResource('/crud/waliasuh', WaliasuhController::class);
    Route::apiResource('/ceud/anakasuh', AnakasuhController::class);
    Route::get('/waliasuh', [WaliasuhController::class, 'getAllWaliasuh']);
    Route::get('/waliasuh/{id}', [WaliasuhController::class, 'getDetailWaliasuh']);
    Route::get('/anakasuh', [AnakasuhController::class, 'getAllAnakasuh']);
    Route::get('/anakasuh/{id}', [AnakasuhController::class, 'getDetailAnakasuh']);
    Route::get('/kewaliasuhan/grup', [GrupWaliAsuhController::class, 'getAllGrupWaliasuh']);

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
    Route::apiResource('/pengurus', PengurusController::class);
    Route::apiResource('/karyawan', KaryawanController::class);
    Route::apiResource('/jenisberkas', JenisBerkasController::class);
    Route::apiResource('/materiAjar', MateriAjarController::class);
    Route::get('/berkas', [BerkasController::class, 'Berkas']);
    Route::get('/pengajars', [PengajarController::class, 'getallPengajar']);
    Route::get('/pengurus', [PengurusController::class, 'dataPengurus']);
    Route::get('/walikelas', [WalikelasController::class, 'getDataWalikelas']);
    Route::get('/karyawans', [KaryawanController::class, 'dataKaryawan']);
    Route::get('/pegawais', [PegawaiController::class, 'dataPegawai']);
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
    Route::get('/wilayah', [DropdownController::class, 'menuWilayahBlokKamar']);
    Route::get('/negara', [DropdownController::class, 'menuNegaraProvinsiKabupatenKecamatan']);
    Route::get('/lembaga', [DropdownController::class, 'menuLembagaJurusanKelasRombel']);
    Route::get('/angkatan', [DropdownController::class, 'getAngkatan']);
    Route::get('/periode', [DropdownController::class, 'getPeriodeOptions']);
    Route::get('/golongan', [DropdownController::class, 'menuKategoriGolonganAndGolongan']);
    Route::get('/materi-ajar', [DropdownController::class, 'menuMateriAjar']);
});
