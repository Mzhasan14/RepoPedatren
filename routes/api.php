<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\Auth\AuthController;

use App\Http\Controllers\Api\Administrasi\{
    CatatanAfektifController as AdministrasiCatatanAfektifController,
    CatatanKognitifController,
    DetailPelanggaranController,
    PerizinanController,
    PelanggaranController,
    DetailPerizinanController,
};
use App\Http\Controllers\Api\PesertaDidik\{
    AnakPegawaiController,
    PesertaDidikController,
    PelajarController,
    SantriController,
    AlumniController,
    DetailPesertaDidikController,
    KhadamController,
    BersaudaraController,
    NonDomisiliController
};

use App\Http\Controllers\api\PesertaDidik\formulir\{
    DomisiliController,
    PendidikanController,
    BiodataController,
    WargaPesantrenController,
    BerkasController,
    StatusSantriController
};

use App\Http\Controllers\Api\keluarga\{
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
use App\Http\Controllers\api\Biometric\BiometricScanController;
use App\Http\Controllers\Api\Kewaliasuhan\{
    GrupWaliAsuhController,
    WaliasuhController,
    AnakasuhController
};
use App\Http\Controllers\Api\Wilayah\{
    WilayahController,
    BlokController,
    KamarController,
};
use App\Http\Controllers\Api\Pendidikan\{
    LembagaController,
    JurusanController,
    KelasController,
    RombelController
};
use App\Http\Controllers\Api\Pegawai\{
    DetailKepegawaianController,
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

// Endpoint menampilkan log
Route::middleware(['auth:sanctum', 'role:admin', 'log.activity'])
    ->get('activity-logs', function () {
        return \Spatie\Activitylog\Models\Activity::with('causer', 'subject')
            ->where('log_name', 'api')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    });


// Formulir Peserta Didik
Route::prefix('formulir')->middleware('auth:sanctum', 'role:superadmin|admin')->group(function () {
    // Biodata
    Route::get('/{id}/biodata/edit', [BiodataController::class, 'edit']);
    Route::post('/biodata', [BiodataController::class, 'store']);
    Route::put('/{id}/biodata', [BiodataController::class, 'update']);

    // Santri
    Route::get('/{bioId}/santri', [StatusSantriController::class, 'index']);
    Route::get('{id}/santri/edit', [StatusSantriController::class, 'edit']);
    Route::post('/{id}/santri', [StatusSantriController::class, 'store']);
    Route::put('/{id}/santri', [StatusSantriController::class, 'update']);

    // Domisili
    Route::get('/{bioId}/domisili', [DomisiliController::class, 'index']);
    Route::get('{id}/domisili/edit', [DomisiliController::class, 'edit']);
    Route::post('/{id}/domisili', [DomisiliController::class, 'store']);
    Route::put('/{id}/domisili', [DomisiliController::class, 'update']);

    // Pendidikan
    Route::get('/{bioId}/pendidikan', [PendidikanController::class, 'index']);
    Route::get('{id}/pendidikan/edit', [PendidikanController::class, 'edit']);
    Route::post('/{id}/pendidikan', [PendidikanController::class, 'store']);
    Route::put('/{id}/pendidikan', [PendidikanController::class, 'update']);

    // Warga Pesantren
    Route::get('/{id}/wargapesantren/edit', [WargaPesantrenController::class, 'edit']);
    Route::post('/{id}/wargapesantren', [WargaPesantrenController::class, 'store']);
    Route::put('/{id}/wargapesantren', [WargaPesantrenController::class, 'update']);

    // Berkas
    Route::get('/{bioId}/berkas', [BerkasController::class, 'index']);
    Route::get('/{id}/berkas/edit', [BerkasController::class, 'edit']);
    Route::post('/{id}/berkas', [BerkasController::class, 'store']);
    Route::put('/{id}/berkas', [BerkasController::class, 'update']);

    // Kepegawaian
    Route::get('{id}/karyawan', [KaryawanController::class, 'index']);
    Route::get('/{id}/karyawan/edit', [KaryawanController::class, 'edit']);
    Route::put('/{id}/karyawan', [KaryawanController::class, 'update']);
    Route::post('/{id}/karyawan', [KaryawanController::class, 'store']);
    Route::get('/{id}/pengajar', [PengajarController::class, 'index']);
    Route::get('/{id}/pengajar/edit', [PengajarController::class, 'edit']);
    Route::put('/{id}/pengajar', [PengajarController::class, 'update']);
    Route::post('/{id}/pengajar', [PengajarController::class, 'store']);
    Route::get('/{id}/pengurus', [PengurusController::class, 'index']);
    Route::get('/{id}/pengurus/edit', [PengurusController::class, 'edit']);
    Route::put('/{id}/pengurus', [PengurusController::class, 'update']);
    Route::post('/{id}/pengurus', [PengurusController::class, 'store']);

    // Keluarga
    Route::get('/keluarga', [KeluargaController::class, 'index']);
    Route::post('/orangtua',[OrangTuaWaliController::class,'store']);
    Route::get('/{id}/orangtua', [OrangTuaWaliController::class, 'edit']);
    Route::put('/orangtua/{id}', [OrangTuaWaliController::class, 'update']);
});

Route::post('register', [AuthController::class, 'register'])->middleware('auth:sanctum','role:admin|superadmin');
Route::post('login',    [AuthController::class, 'login'])->middleware('throttle:7,1')->name('login');
Route::post('forgot',   [AuthController::class, 'forgotPassword']);
Route::post('reset',    [AuthController::class, 'resetPassword'])->name('password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',  [AuthController::class, 'logout']);
    Route::patch('profile', [AuthController::class, 'updateProfile']);
    Route::post('password', [AuthController::class, 'changePassword']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Biometric
Route::prefix('biometric')->group(function () {
    Route::post('/register', [BiometricScanController::class, 'register']);
    Route::post('/scan', [BiometricScanController::class, 'scan']);
});

// Export
Route::prefix('export')->group(function () {
    Route::get('/pesertadidik', [PesertaDidikController::class, 'pesertaDidikExport'])->name('pesertadidik.export');
    Route::get('/alumni', [AlumniController::class, 'alumniExport'])->name('alumni.export');
    Route::get('/khadam', [KhadamController::class, 'khadamExport'])->name('khadam.export');
});

Route::prefix('crud')->middleware('auth:sanctum')->group(function () {
    Route::post('/pesertadidik', [PesertaDidikController::class, 'store']);
    Route::put('/pesertadidik/{id}', [PesertaDidikController::class, 'update']);
    Route::delete('/pesertadidik/{id}', [PesertaDidikController::class, 'destroy']);
    Route::post('/set-alumni-santri', [AlumniController::class, 'setAlumniSantri']);
    Route::post('/set-alumni-pelajar', [AlumniController::class, 'setAlumniPelajar']);

    //perizinan
    Route::get('/{id}/perizinan', [PerizinanController::class, 'index']);
    Route::get('/{id}/perizinan/edit', [PerizinanController::class, 'edit']);
    Route::post('/{id}/perizinan', [PerizinanController::class, 'store']);
    Route::put('/{id}/perizinan', [PerizinanController::class, 'update']);

    //pelanggaran
    Route::get('/{id}/pelanggaran', [PelanggaranController::class, 'index']);
    Route::get('/{id}/pelanggaran/edit', [PelanggaranController::class, 'edit']);
    Route::post('/{id}/pelanggaran', [PelanggaranController::class, 'store']);
    Route::put('/{id}/pelanggaran', [PelanggaranController::class, 'update']);

    //Kewaliasuhan
    Route::post('/grupwaliasuh', [GrupWaliAsuhController::class, 'store']);
    Route::put('/grupwaliasuh/{id}', [GrupWaliAsuhController::class, 'update']);
});

Route::prefix('data-pokok')->group(function () {

    // 🏫 Santri & Peserta Didik
    Route::get('/pesertadidik', [PesertaDidikController::class, 'getAllPesertaDidik']);
    Route::get('/pesertadidik-bersaudara', [BersaudaraController::class, 'getAllBersaudara']);
    Route::get('/pesertadidik-bersaudara/{id}', [DetailPesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/pesertadidik/{id}', [DetailPesertaDidikController::class, 'getDetailPesertaDidik']);
    Route::get('/santri', [SantriController::class, 'getAllSantri']);
    Route::get('/santri-nondomisili', [NonDomisiliController::class, 'getNonDomisili']);
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
    Route::get('/keluarga/{id_biodata}', [KeluargaController::class, 'getKeluargaByIdBio']);
    Route::get('/orangtua', [OrangTuaWaliController::class, 'getAllOrangtua']);
    Route::get('/orangtua/{id}', [OrangTuaWaliController::class, 'getDetailOrangtua']);
    Route::get('/wali', [WaliController::class, 'getAllWali']);
    Route::get('/wali/{id}', [WaliController::class, 'getDetailWali']);

    // 📍 Alamat
    Route::apiResource('/provinsi', ProvinsiController::class);
    Route::apiResource('/kabupaten', KabupatenController::class);
    Route::apiResource('/kecamatan', KecamatanController::class);

    // 🏠 Kewaliasuhan (Asrama/Pengasuhan)
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
    // Route::apiResource('/pegawai', PegawaiController::class);
    Route::apiResource('/pengajar', PengajarController::class);
    Route::apiResource('/walikelas', WalikelasController::class);
    Route::apiResource('/kategori-golongan', KategoriGolonganController::class);
    Route::apiResource('/golongan', GolonganController::class);
    Route::apiResource('/pengurus', PengurusController::class);
    Route::apiResource('/karyawan', KaryawanController::class);
    Route::apiResource('/materiAjar', MateriAjarController::class);
    Route::get('/pengajars', [PengajarController::class, 'getallPengajar']);
    Route::get('/pengurus', [PengurusController::class, 'dataPengurus']);
    Route::get('/walikelas', [WalikelasController::class, 'getDataWalikelas']);
    Route::get('/karyawans', [KaryawanController::class, 'dataKaryawan']);
    Route::get('/pegawais', [PegawaiController::class, 'dataPegawai']);
    Route::get('/pengurus/{id}', [DetailKepegawaianController::class, 'getAllKepegawaian']);
    Route::get('/pengajar/{id}', [DetailKepegawaianController::class, 'getAllKepegawaian']);
    Route::get('/karyawan/{id}', [DetailKepegawaianController::class, 'getAllKepegawaian']);
    Route::get('pegawai/{id}', [DetailKepegawaianController::class, 'getAllKepegawaian']);
    Route::get('/walikelas/{id}', [DetailKepegawaianController::class, 'getAllKepegawaian']);
    Route::post('pegawai', [PegawaiController::class, 'store']);
});

Route::prefix('dropdown')->group(function () {
    Route::get('/golongan-jabatan', [DropdownController::class, 'getGolonganJabatan']);
    Route::get('/satuan-kerja', [DropdownController::class, 'getSatuanKerja']);
    Route::get('/wilayah', [DropdownController::class, 'menuWilayahBlokKamar']);
    Route::get('/negara', [DropdownController::class, 'menuNegaraProvinsiKabupatenKecamatan']);
    Route::get('/lembaga', [DropdownController::class, 'menuLembagaJurusanKelasRombel']);
    Route::get('/angkatan', [DropdownController::class, 'getAngkatan']);
    Route::get('/periode', [DropdownController::class, 'getPeriodeOptions']);
    Route::get('/golongan', [DropdownController::class, 'menuKategoriGolonganAndGolongan']);
    Route::get('/materi-ajar', [DropdownController::class, 'menuMateriAjar']);
});
