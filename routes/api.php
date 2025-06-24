<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ActivityController;
use App\Http\Controllers\api\Auth\AuthController;
use App\Http\Controllers\api\Auth\UserController;
use App\Http\Controllers\api\wilayah\BlokController;
use App\Http\Controllers\api\keluarga\WaliController;
use App\Http\Controllers\api\wilayah\KamarController;
use App\Http\Controllers\api\Pegawai\PegawaiController;
use App\Http\Controllers\api\wilayah\WilayahController;
use App\Http\Controllers\api\Pegawai\DropdownController;
use App\Http\Controllers\api\Pegawai\GolonganController;
use App\Http\Controllers\api\Pegawai\KaryawanController;
use App\Http\Controllers\api\Pegawai\PengajarController;
use App\Http\Controllers\api\Pegawai\PengurusController;
use App\Http\Controllers\api\pendidikan\KelasController;
use App\Http\Controllers\api\keluarga\KeluargaController;
use App\Http\Controllers\api\Pegawai\WalikelasController;
use App\Http\Controllers\api\pendidikan\RombelController;
use App\Http\Controllers\api\pendidikan\JurusanController;
use App\Http\Controllers\api\pendidikan\LembagaController;
use App\Http\Controllers\api\PesertaDidik\AlumniController;
use App\Http\Controllers\api\PesertaDidik\DetailController;
use App\Http\Controllers\api\PesertaDidik\KhadamController;
use App\Http\Controllers\api\PesertaDidik\SantriController;
use App\Http\Controllers\api\PesertaDidik\PelajarController;
use App\Http\Controllers\api\keluarga\OrangTuaWaliController;
use App\Http\Controllers\api\kewaliasuhan\AnakasuhController;
use App\Http\Controllers\api\kewaliasuhan\WaliasuhController;
use App\Http\Controllers\api\Administrasi\PerizinanController;
use App\Http\Controllers\api\Pegawai\GolonganJabatanController;
use App\Http\Controllers\api\PesertaDidik\BersaudaraController;
use App\Http\Controllers\api\Administrasi\PelanggaranController;
use App\Http\Controllers\api\Pegawai\KategoriGolonganController;
use App\Http\Controllers\api\PesertaDidik\AnakPegawaiController;
use App\Http\Controllers\api\PesertaDidik\NonDomisiliController;
use App\Http\Controllers\api\keluarga\HubunganKeluargaController;
use App\Http\Controllers\api\kewaliasuhan\GrupWaliAsuhController;
use App\Http\Controllers\api\PesertaDidik\PesertaDidikController;
use App\Http\Controllers\api\Biometric\BiometricProfileController;
use App\Http\Controllers\api\Administrasi\CatatanKognitifController;
use App\Http\Controllers\api\Administrasi\DetailPerizinanController;
use App\Http\Controllers\api\PesertaDidik\Formulir\BerkasController;
use App\Http\Controllers\api\Administrasi\DetailPengunjungController;
use App\Http\Controllers\api\Administrasi\PengunjungMahromController;
use App\Http\Controllers\api\pendidikan\DropdownPendidikanController;
use App\Http\Controllers\api\PesertaDidik\DropDownAngkatanController;
use App\Http\Controllers\api\PesertaDidik\Formulir\BiodataController;
use App\Http\Controllers\api\Administrasi\DetailPelanggaranController;
use App\Http\Controllers\api\PesertaDidik\Formulir\DomisiliController;
use App\Http\Controllers\api\PesertaDidik\Formulir\KhadamFormController;
use App\Http\Controllers\api\PesertaDidik\Formulir\PendidikanController;
use App\Http\Controllers\api\PesertaDidik\Formulir\StatusSantriController;
use App\Http\Controllers\api\PesertaDidik\Formulir\WargaPesantrenController;
use App\Http\Controllers\api\Administrasi\CatatanAfektifController as AdministrasiCatatanAfektifController;
use App\Http\Controllers\Api\Pegawai\MataPelajaranController;

// Auth
Route::post('register', [AuthController::class, 'register'])
    ->middleware(['auth:sanctum', 'role:admin|superadmin', 'throttle:5,1']);

Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('login');

Route::post('forgot', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:5,1');

Route::post('reset', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:5,1')
    ->name('password.reset');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::patch('profile', [AuthController::class, 'updateProfile']);
    Route::post('password', [AuthController::class, 'changePassword']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::prefix('data-pokok')->middleware(['auth:sanctum', 'throttle:200,1'])->group(function () {
    // 🏫 Santri & Peserta Didik
    Route::get('/pesertadidik', [PesertaDidikController::class, 'getAllPesertaDidik']);
    Route::get('/pesertadidik-bersaudara', [BersaudaraController::class, 'getAllBersaudara']);
    Route::get('/pesertadidik-bersaudara/{id}', [DetailController::class, 'getDetail']);
    Route::get('/pesertadidik/{id}', [DetailController::class, 'getDetail']);
    Route::get('/santri', [SantriController::class, 'getAllSantri']);
    Route::get('/santri-nondomisili', [NonDomisiliController::class, 'getNonDomisili']);
    Route::get('/santri-nondomisili/{id}', [DetailController::class, 'getDetail']);
    Route::get('/santri/{id}', [DetailController::class, 'getDetail']);
    Route::get('/pelajar', [PelajarController::class, 'getAllPelajar']);
    Route::get('/pelajar/{id}', [DetailController::class, 'getDetail']);
    Route::get('/alumni', [AlumniController::class, 'alumni']);
    Route::get('/alumni/{id}', [DetailController::class, 'getDetail']);
    Route::get('/anakpegawai', [AnakPegawaiController::class, 'getAllAnakpegawai']);
    Route::get('/anakpegawai/{id}', [DetailController::class, 'getDetail']);

    // Khadam
    Route::get('/khadam', [KhadamController::class, 'getAllKhadam']);
    Route::get('/khadam/{id}', [DetailController::class, 'getDetail']);

    // Pengunjung mahrom
    Route::get('/pengunjung', [PengunjungMahromController::class, 'getAllPengunjung']);
    Route::get('/pengunjung/{id}', [DetailPengunjungController::class, 'getDetailPengunjung']);

    // 🚨 Administrasi
    Route::get('/perizinan', [PerizinanController::class, 'getAllPerizinan']);
    Route::get('/perizinan/{id}', [DetailPerizinanController::class, 'getDetailPerizinan']);
    Route::get('/pelanggaran', [PelanggaranController::class, 'getAllPelanggaran']);
    Route::get('/pelanggaran/{id}', [DetailPelanggaranController::class, 'getDetailPelanggaran']);

    Route::get('/catatan-afektif', [AdministrasiCatatanAfektifController::class, 'getCatatanAfektif']);
    Route::get('/catatan-kognitif', [CatatanKognitifController::class, 'getCatatanKognitif']);
    Route::get('/catatan-kognitif/{id}', [DetailController::class, 'getDetail']);
    Route::get('/catatan-afektif/{id}', [DetailController::class, 'getDetail']);

    // 🏫 Keluarga
    Route::get('/keluarga', [KeluargaController::class, 'getAllKeluarga']);
    Route::get('/orangtua', [OrangTuaWaliController::class, 'getAllOrangtua']);
    Route::get('/orangtua/{id}', [OrangTuaWaliController::class, 'getDetailOrangtua']);
    Route::get('/wali', [WaliController::class, 'getAllWali']);
    Route::get('/wali/{id}', [WaliController::class, 'getDetailWali']);

    // 🏠 Kewaliasuhan (Asrama/Pengasuhan)
    Route::get('/waliasuh', [WaliasuhController::class, 'getAllWaliasuh']);
    Route::get('/waliasuh/{id}', [WaliasuhController::class, 'getDetailWaliasuh']);
    Route::get('/anakasuh', [AnakasuhController::class, 'getAllAnakasuh']);
    Route::get('/anakasuh/{id}', [AnakasuhController::class, 'getDetailAnakasuh']);
    Route::get('/kewaliasuhan/grup', [GrupWaliAsuhController::class, 'getAllGrupWaliasuh']);

    // 👨‍🏫 Pegawai & Guru
    Route::get('/pengajar', [PengajarController::class, 'getallPengajar']);
    Route::get('/pengurus', [PengurusController::class, 'dataPengurus']);
    Route::get('/walikelas', [WalikelasController::class, 'getDataWalikelas']);
    Route::get('/karyawan', [KaryawanController::class, 'dataKaryawan']);
    Route::get('/pegawai', [PegawaiController::class, 'dataPegawai']);
    Route::post('pegawai', [PegawaiController::class, 'store']);

    Route::get('/pengurus/{id}', [DetailController::class, 'getDetail']);
    Route::get('/pengajar/{id}', [DetailController::class, 'getDetail']);
    Route::get('/karyawan/{id}', [DetailController::class, 'getDetail']);
    Route::get('pegawai/{id}', [DetailController::class, 'getDetail']);
    Route::get('/walikelas/{id}', [DetailController::class, 'getDetail']);
});

// Export
Route::prefix('export')->middleware([ 'throttle:15,1'])->group(function () {
    Route::get('/pesertadidik', [PesertaDidikController::class, 'exportExcel'])->name('pesertadidik.export');
    Route::get('/santri', [SantriController::class, 'exportExcel'])->name('santri.export');
    Route::get('/santri-nondomisili', [NonDomisiliController::class, 'exportExcel'])->name('nondomisili.export');
    Route::get('/pelajar', [PelajarController::class, 'exportExcel'])->name('pelajar.export');
    Route::get('/bersaudara', [BersaudaraController::class, 'exportExcel'])->name('bersaudara.export');
    Route::get('/khadam', [KhadamController::class, 'exportExcel'])->name('khadam.export');
    Route::get('/perizinan', [PerizinanController::class, 'exportExcel'])->name('perizinan.export');
    Route::get('/alumni', [AlumniController::class, 'exportExcel'])->name('alumni.export');
    Route::get('/anakpegawai', [AnakPegawaiController::class, 'exportExcel'])->name('anakpegawai.export');
    Route::get('/pegawai', [PegawaiController::class, 'exportExcel'])->name('pegawai.export');
    Route::get('/karyawan', [KaryawanController::class, 'KaryawanExcel'])->name('karyawan.export');
    Route::get('/pengajar', [PengajarController::class, 'pengajarExport'])->name('pengajar.export');
    Route::get('/pengurus', [PengurusController::class, 'pengurusExport'])->name('pengurus.export');
});

// Formulir Peserta Didik
Route::prefix('formulir')->middleware([
    'auth:sanctum',
    'role:superadmin|admin',
    'throttle:200,1'
])->group(function () {

    // Biodata
    Route::get('/{id}/biodata/show', [BiodataController::class, 'show']);
    Route::put('/{id}/biodata', [BiodataController::class, 'update']);
    // Santri
    Route::get('/{bioId}/santri', [StatusSantriController::class, 'index']);
    Route::get('{id}/santri/show', [StatusSantriController::class, 'show']);
    Route::post('/{id}/santri', [StatusSantriController::class, 'store']);
    Route::put('/{id}/santri', [StatusSantriController::class, 'update']);

    // Domisili
    Route::get('/{bioId}/domisili', [DomisiliController::class, 'index']);
    Route::get('{id}/domisili/show', [DomisiliController::class, 'show']);
    Route::post('/{id}/domisili', [DomisiliController::class, 'store']);
    Route::put('/{id}/domisili', [DomisiliController::class, 'update'])->middleware('role:superadmin|admin');

    Route::put('/{id}/domisili/pindah', [DomisiliController::class, 'pindahDomisili']);
    Route::put('/{id}/domisili/keluar', [DomisiliController::class, 'keluarDomisili']);

    // Pendidikan
    Route::get('/{bioId}/pendidikan', [PendidikanController::class, 'index']);
    Route::get('{id}/pendidikan/show', [PendidikanController::class, 'show']);
    Route::post('/{id}/pendidikan', [PendidikanController::class, 'store']);
    Route::put('/{id}/pendidikan', [PendidikanController::class, 'update'])->middleware('role:superadmin|admin');

    Route::put('/{id}/pendidikan/pindah', [PendidikanController::class, 'pindahPendidikan']);
    Route::put('/{id}/pendidikan/keluar', [PendidikanController::class, 'keluarPendidikan']);

    // Kewaliasuhan
    Route::get('{bioId}/waliasuh', [WaliasuhController::class, 'index']);
    Route::get('{id}/waliasuh/show', [WaliasuhController::class, 'show']);
    Route::post('{id}/waliasuh', [WaliasuhController::class, 'store']);
    Route::put('{id}/waliasuh', [WaliasuhController::class, 'update'])->middleware('role:superadmin|admin');

    Route::put('{id}/waliasuh/keluar', [WaliasuhController::class, 'keluarWaliasuh']);

    Route::get('{bioId}/anakasuh', [AnakasuhController::class, 'index']);
    Route::get('{id}/anakasuh/show', [AnakasuhController::class, 'show']);
    Route::post('{id}/anakasuh', [AnakAsuhController::class, 'formStore']);

    Route::put('{id}/anakasuh/pindah', [AnakasuhController::class, 'pindahAnakasuh']);
    Route::put('{id}/anakasuh/keluar', [AnakasuhController::class, 'keluarAnakasuh']);
    
    // Khadam
    Route::get('/{bioId}/khadam', [KhadamFormController::class, 'index']);
    Route::get('{id}/khadam/show', [KhadamFormController::class, 'show']);
    Route::post('/{id}/khadam', [KhadamFormController::class, 'store']);
    Route::put('/{id}/khadam', [KhadamFormController::class, 'update'])->middleware('role:superadmin|admin');

    Route::put('/{id}/khadam/pindah', [KhadamFormController::class, 'pindahKhadam']);
    Route::put('/{id}/khadam/keluar', [KhadamFormController::class, 'keluarKhadam']);

    // Warga Pesantren
    Route::get('/{id}/wargapesantren', [WargaPesantrenController::class, 'index']);
    Route::get('/{id}/wargapesantren/show', [WargaPesantrenController::class, 'show']);
    Route::post('/{id}/wargapesantren', [WargaPesantrenController::class, 'store']);
    Route::put('/{id}/wargapesantren', [WargaPesantrenController::class, 'update']);

    // Berkas
    Route::get('/{bioId}/berkas', [BerkasController::class, 'index']);
    Route::get('/{id}/berkas/show', [BerkasController::class, 'show']);
    Route::post('/{id}/berkas', [BerkasController::class, 'store']);
    Route::put('/{id}/berkas', [BerkasController::class, 'update']);

    // Kepegawaian
    Route::get('{id}/karyawan', [KaryawanController::class, 'index']);
    Route::get('/{id}/karyawan/show', [KaryawanController::class, 'edit']);
    Route::put('/{id}/karyawan', [KaryawanController::class, 'update']);
    Route::post('/{id}/karyawan', [KaryawanController::class, 'store']);

    Route::get('/{id}/pengajar', [PengajarController::class, 'index']);
    Route::get('/{id}/pengajar/show', [PengajarController::class, 'edit']);
    Route::put('/{id}/pengajar', [PengajarController::class, 'update']);
    Route::post('/{id}/pengajar', [PengajarController::class, 'store']);
    Route::delete('/{pengajarId}/pengajar/materi/{materiId}/nonaktifkan', [PengajarController::class, 'nonaktifkan']);
    Route::post('/{pengajarId}/pengajar/materi', [PengajarController::class, 'tambahMateri']);
    Route::get('/{materiId}/show', [PengajarController::class, 'showMateri']);
    Route::put('/{materiId}/update', [PengajarController::class, 'updateMateri']);

    Route::get('/mata-pelajaran', [MataPelajaranController::class, 'getAllMapel']);
    Route::post('/mata-pelajaran', [MataPelajaranController::class, 'createMataPelajaran']);
    Route::delete('/{materiId}/mata-pelajaran', [MataPelajaranController::class, 'DestroyMapel']);
    Route::get('/{materiId}/jadwal-pelajaran', [PengajarController::class, 'showByMateriId']);
    Route::post('/{materiId}/jadwal-pelajaran/simpan', [PengajarController::class, 'simpan']);
    Route::delete('/{jadwalId}/jadwal-pelajaran/hapus', [PengajarController::class, 'hapus']);
    Route::put('/{jadwalId}/jadwal-pelajaran/update', [PengajarController::class, 'updateJadwal']);

    Route::get('/{id}/pengurus', [PengurusController::class, 'index']);
    Route::get('/{id}/pengurus/show', [PengurusController::class, 'edit']);
    Route::put('/{id}/pengurus', [PengurusController::class, 'update']);
    Route::post('/{id}/pengurus', [PengurusController::class, 'store']);

    Route::get('/{id}/walikelas', [WalikelasController::class, 'index']);
    Route::get('/{id}/walikelas/show', [WalikelasController::class, 'edit']);
    Route::put('/{id}/walikelas', [WalikelasController::class, 'update']);
    Route::post('/{id}/walikelas', [WalikelasController::class, 'store']);

    Route::put('/{id}/karyawan/pindah', [KaryawanController::class, 'pindahKaryawan']);
    Route::put('/{id}/karyawan/keluar', [KaryawanController::class, 'keluarKaryawan']);

    Route::put('/{id}/pengajar/pindah', [PengajarController::class, 'pindahPengajar']);
    Route::put('/{id}/pengajar/keluar', [PengajarController::class, 'keluarPengajar']);

    Route::put('/{id}/pengurus/pindah', [PengurusController::class, 'pindahPengurus']);
    Route::put('/{id}/pengurus/keluar', [PengurusController::class, 'keluarPengurus']);

    Route::put('/{id}/catatan-afektif/keluar', [AdministrasiCatatanAfektifController::class, 'keluarAfektif']);
    Route::put('/{id}/catatan-kognitif/keluar', [CatatanKognitifController::class, 'keluarKognitif']);

    Route::put('/{id}/walikelas/pindah', [WalikelasController::class, 'pindahWalikelas']);
    Route::put('/{id}/walikelas/keluar', [WalikelasController::class, 'keluarWalikelas']);

    // Keluarga
    Route::get('{bioId}/keluarga', [KeluargaController::class, 'index']);
    Route::get('{id}/keluarga/show', [KeluargaController::class, 'show']);
    Route::put('{id}/keluarga', [KeluargaController::class, 'update']); // update hanya 1 data keluarga saja
    Route::put('{id}/keluarga/pindah', [KeluargaController::class, 'pindahkanSeluruhKk']); // jika ingin memindahkan seluruh anggota keluarga ke nomor kk baru

    Route::get('/{bioId}/orangtua', [OrangTuaWaliController::class, 'index']);
    Route::post('/orangtua', [OrangTuaWaliController::class, 'store']);
    Route::get('/{id}/orangtua/show', [OrangTuaWaliController::class, 'show']);
    Route::get('/{id}/orangtua', [OrangTuaWaliController::class, 'edit']);
    Route::put('{id}/orangtua', [OrangTuaWaliController::class, 'update'])->middleware('role:superadmin|admin');
    Route::delete('/orangtua/{id}', [OrangTuaWaliController::class, 'destroy'])->middleware('role:superadmin|admin');

    // Catatan Santri
    Route::get('/{BioId}/catatan-afektif', [AdministrasiCatatanAfektifController::class, 'index']);
    Route::get('/{id}/catatan-afektif/show', [AdministrasiCatatanAfektifController::class, 'edit']);
    Route::put('/{id}/catatan-afektif', [AdministrasiCatatanAfektifController::class, 'update']);
    Route::post('/{BioId}/catatan-afektif', [AdministrasiCatatanAfektifController::class, 'store']);

    Route::get('/{BioId}/catatan-kognitif', [CatatanKognitifController::class, 'index']);
    Route::get('/{id}/catatan-kognitif/show', [CatatanKognitifController::class, 'edit']);
    Route::put('/{id}/catatan-kognitif', [CatatanKognitifController::class, 'update']);
    Route::post('/{BioId}/catatan-kognitif', [CatatanKognitifController::class, 'store']);
});

Route::prefix('crud')->middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    Route::post('/pesertadidik', [PesertaDidikController::class, 'store']);
    Route::delete('/pesertadidik/{id}', [PesertaDidikController::class, 'destroy']);

    // Create Anak Pegawai
    Route::post('/anakpegawai', [AnakPegawaiController::class, 'store']);

    // Create Khadam
    Route::post('/khadam', [KhadamController::class, 'store']);

    Route::post('/set-alumni-santri', [AlumniController::class, 'setAlumniSantri']);
    Route::post('/set-alumni-pelajar', [AlumniController::class, 'setAlumniPelajar']);

    // perizinan
    Route::get('/{id}/perizinan', [PerizinanController::class, 'index']);
    Route::get('/{id}/perizinan/show', [PerizinanController::class, 'show']);
    Route::post('/{id}/perizinan', [PerizinanController::class, 'store']);
    Route::put('/{id}/perizinan', [PerizinanController::class, 'update']);

    Route::put('/{id}/perizinan/set-keluar', [PerizinanController::class, 'setKeluar']);
    Route::put('/{id}/perizinan/set-kembali', [PerizinanController::class, 'setKembali']);

    Route::post('/{id}/berkas-perizinan', [PerizinanController::class, 'addBerkasPerizinan']);

    // pelanggaran
    Route::get('/{id}/pelanggaran', [PelanggaranController::class, 'index']);
    Route::get('/{id}/pelanggaran/show', [PelanggaranController::class, 'show']);
    Route::post('/{id}/pelanggaran', [PelanggaranController::class, 'store']);
    Route::put('/{id}/pelanggaran', [PelanggaranController::class, 'update']);

    Route::post('/{id}/berkas-pelanggaran', [PelanggaranController::class, 'addBerkasPelanggaran']);

    // //pengunjung mahrom
    // Route::get('/{id}/pengunjung', [PengunjungMahromController::class, 'index']);
    Route::get('/{id}/pengunjung/show', [PengunjungMahromController::class, 'show']);
    Route::post('/pengunjung', [PengunjungMahromController::class, 'store']);
    Route::put('/pengunjung/{id}', [PengunjungMahromController::class, 'update']);

    // Keluarga
    Route::get('/hubungan', [HubunganKeluargaController::class, 'index']);
    Route::post('/hubungan', [HubunganKeluargaController::class, 'store']);
    Route::get('{id}/hubungan/show', [HubunganKeluargaController::class, 'show']);
    Route::put('{id}/hubungan', [HubunganKeluargaController::class, 'update']);
    Route::delete('{id}/hubungan', [HubunganKeluargaController::class, 'destroy']);

    // Kewaliasuhan
    Route::get('/grupwaliasuh', [GrupWaliAsuhController::class, 'index']);
    Route::post('/grupwaliasuh', [GrupWaliAsuhController::class, 'store']);
    Route::get('{id}/grupwaliasuh/show', [GrupWaliAsuhController::class, 'show']);
    Route::put('/grupwaliasuh/{id}', [GrupWaliAsuhController::class, 'update']);
    Route::delete('/grupwaliasuh/{id}', [GrupWaliAsuhController::class, 'destroy']);
    Route::put('/grupwaliasuh/{id}/activate', [GrupWaliAsuhController::class, 'activate']);

    // lembaga
    Route::get('lembaga', [LembagaController::class, 'index']);
    Route::post('lembaga', [LembagaController::class, 'store']);
    Route::get('{id}/lembaga/edit', [LembagaController::class, 'edit']);
    Route::put('{id}/lembaga', [LembagaController::class, 'update']);
    Route::delete('{id}/lembaga', [LembagaController::class, 'destroy']);

    // 🏠 Wilayah (Blok, Kamar, Domisili)
    Route::apiResource('/wilayah', WilayahController::class);
    Route::put('/wilayah/{id}/activate', [WilayahController::class, 'activate']);
    Route::apiResource('/blok', BlokController::class);
    Route::put('/blok/{id}/activate', [BlokController::class, 'activate']);
    Route::apiResource('/kamar', KamarController::class);
    Route::put('/kamar/{id}/activate', [KamarController::class, 'activate']);

    // 🎓 Pendidikan
    Route::apiResource('/lembaga', LembagaController::class);
    Route::put('/lembaga/{id}/activate', [LembagaController::class, 'activate']);
    Route::apiResource('/jurusan', JurusanController::class);
    Route::put('/jurusan/{id}/activate', [JurusanController::class, 'activate']);
    Route::apiResource('/kelas', KelasController::class);
    Route::put('/kelas/{id}/activate', [KelasController::class, 'activate']);
    Route::apiResource('/rombel', RombelController::class);
    Route::put('/rombel/{id}/activate', [RombelController::class, 'activate']);

    // golongan
    Route::get('golongan', [GolonganController::class, 'index']);
    Route::post('golongan', [GolonganController::class, 'store']);
    Route::get('{id}/golongan/edit', [GolonganController::class, 'edit']);
    Route::put('{id}/golongan', [GolonganController::class, 'update']);
    Route::delete('{id}/golongan', [GolonganController::class, 'destroy']);

    // kategori golongan
    Route::get('kategori-golongan', [KategoriGolonganController::class, 'index']);
    Route::post('kategori-golongan', [KategoriGolonganController::class, 'store']);
    Route::get('{id}/kategori-golongan/edit', [KategoriGolonganController::class, 'edit']);
    Route::put('{id}/kategori-golongan', [KategoriGolonganController::class, 'update']);
    Route::delete('{id}/kategori-golongan', [KategoriGolonganController::class, 'destroy']);

    // golongan Jabatan
    Route::get('golongan-jabatan', [GolonganJabatanController::class, 'index']);
    Route::post('golongan-jabatan', [GolonganJabatanController::class, 'store']);
    Route::get('{id}/golongan-jabatan/edit', [GolonganJabatanController::class, 'edit']);
    Route::put('{id}/golongan-jabatan', [GolonganJabatanController::class, 'update']);
    Route::delete('{id}/golongan-jabatan', [GolonganJabatanController::class, 'destroy']);

    // Kepegawaian
    Route::post('/pegawai', [PegawaiController::class, 'store']);
    Route::post('/catatan-afektif', [AdministrasiCatatanAfektifController::class, 'CreateStore']);
    Route::post('/catatan-kognitif', [CatatanKognitifController::class, 'storeCatatanKognitif']);

        // Jam Pelajaran
    Route::get('/jam-pelajaran', [MataPelajaranController::class, 'index']);
    Route::post('/jam-pelajaran', [MataPelajaranController::class, 'store']);
    Route::get('/jam-pelajaran/{id}', [MataPelajaranController::class, 'show']);
    Route::put('/jam-pelajaran/{id}', [MataPelajaranController::class, 'update']);
    Route::delete('/jam-pelajaran/{id}', [MataPelajaranController::class, 'destroy']);

    // Jadwal Pelajaran
    Route::get('/jadwal-pelajaran', [MataPelajaranController::class,'getAllJadwal']);
    Route::post('/jadwal-pelajaran', [MataPelajaranController::class,'storeJadwal']);
    Route::get('/jadwal-pelajaran/{id}', [MataPelajaranController::class,'showJadwal']);
    Route::put('/jadwal-pelajaran/{id}', [MataPelajaranController::class,'updateJadwal']);
    Route::delete('/jadwal-pelajaran/batch-delete', [MataPelajaranController::class, 'batchDelete']);

});

Route::prefix('approve')->middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('/perizinan/biktren/{id}', [\App\Http\Controllers\api\Administrasi\ApprovePerizinanController::class, 'approveByBiktren'])->middleware('role:biktren');
    Route::post('/perizinan/kamtib/{id}', [\App\Http\Controllers\api\Administrasi\ApprovePerizinanController::class, 'approveByKamtib'])->middleware('role:kamtib');
    Route::post('/perizinan/pengasuh/{id}', [\App\Http\Controllers\api\Administrasi\ApprovePerizinanController::class, 'approveByPengasuh'])->middleware('role:pengasuh');
});

Route::prefix('fitur')->middleware(['auth:sanctum', 'role:superadmin|admin', 'throttle:60,1'])->group(function () {
    // Pendidikan
    Route::post('/pindah-jenjang', [\App\Http\Controllers\api\PesertaDidik\Fitur\PindahNaikJenjangController::class, 'pindah']);
    Route::post('/naik-jenjang', [\App\Http\Controllers\api\PesertaDidik\Fitur\PindahNaikJenjangController::class, 'naik']);

    // domisili
    Route::post('/pindah-kamar', [\App\Http\Controllers\api\PesertaDidik\Fitur\PindahKamarController::class, 'pindah']);

    // Proses lulus
    Route::post('/proses-lulus', [\App\Http\Controllers\api\PesertaDidik\Fitur\ProsesLulusPendidikanController::class, 'prosesLulus']);
    Route::post('/batal-lulus', [\App\Http\Controllers\api\PesertaDidik\Fitur\ProsesLulusPendidikanController::class, 'batalLulus']);
    Route::get('/list-lulus', [\App\Http\Controllers\api\PesertaDidik\Fitur\ProsesLulusPendidikanController::class, 'listDataLulus']);

    // Proses lulus santri
    Route::post('/proses-alumni', [\App\Http\Controllers\api\PesertaDidik\Fitur\ProsesLulusSantriController::class, 'prosesLulus']);
    Route::post('/batal-alumni', [\App\Http\Controllers\api\PesertaDidik\Fitur\ProsesLulusSantriController::class, 'batalLulus']);
    Route::get('/list-alumni', [\App\Http\Controllers\api\PesertaDidik\Fitur\ProsesLulusSantriController::class, 'listDataLulus']);

    // anak asuh
    Route::post('/anakasuh', [AnakasuhController::class, 'store']);

    // presensi
    Route::get('/presensi-santri', [\App\Http\Controllers\api\PesertaDidik\Fitur\PresensiSantriController::class, 'getAllPresensiSantri']);
    Route::post('/presensi-santri', [\App\Http\Controllers\api\PesertaDidik\Fitur\PresensiSantriController::class, 'store']);
    Route::put('/presensi-santri/{presensi}', [\App\Http\Controllers\api\PesertaDidik\Fitur\PresensiSantriController::class, 'update']);
    Route::delete('/presensi-santri/{presensi}', [\App\Http\Controllers\api\PesertaDidik\Fitur\PresensiSantriController::class, 'destroy']);
});

Route::get('/user', [UserController::class, 'index'])->middleware(['auth:sanctum', 'role:superadmin', 'throttle:20,1']);

Route::prefix('dropdown')->middleware(['auth:sanctum', 'throttle:200,1'])->group(function () {
    Route::get('/golongan-jabatan', [DropdownController::class, 'getGolonganJabatan']);
    Route::get('/satuan-kerja', [DropdownController::class, 'getSatuanKerja']);
    Route::get('/wali-asuh', [DropdownController::class, 'nameWaliasuh']);
    Route::get('/wilayah', [DropdownController::class, 'menuWilayahBlokKamar']);
    Route::get('/negara', [DropdownController::class, 'menuNegaraProvinsiKabupatenKecamatan']);
    Route::get('/lembaga', [DropdownController::class, 'menuLembagaJurusanKelasRombel']);
    Route::get('/angkatan', [DropdownController::class, 'getAngkatan']);
    Route::get('/periode', [DropdownController::class, 'getPeriodeOptions']);
    Route::get('/golongan', [DropdownController::class, 'menuKategoriGolonganAndGolongan']);
    Route::get('/materi-ajar', [DropdownController::class, 'menuMateriAjar']);

    Route::get('/angkatan-santri', [DropDownAngkatanController::class, 'angkatanSantri']);
    Route::get('/angkatan-pelajar', [DropDownAngkatanController::class, 'angkatanPelajar']);

    // Kewaliasuhan
    Route::get('/grup', [GrupWaliasuhController::class, 'getGrup']);
    Route::get('/waliasuh', [WaliasuhController::class, 'getWaliasuh']);

    // Keluarga
    Route::get('hubungan', [HubunganKeluargaController::class, 'getHubungan']);
});

// Endpoint menampilkan log  
Route::get('/activity-logs', [ActivityController::class, 'index']);

// Route::middleware([
//     'auth:sanctum',
//     'role:superadmin|admin',
//     'log.activity',
//     'throttle:100,1'
// ])->get('activity-logs', function () {
//     return \Spatie\Activitylog\Models\Activity::with('causer', 'subject')
//         ->where('log_name', 'api')
//         ->orderBy('created_at', 'desc')
//         ->paginate(20);
// });

// Biometric
Route::prefix('biometric')->middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('register-profile', [BiometricProfileController::class, 'store']);
    Route::post('update-profile', [BiometricProfileController::class, 'update']);
    Route::post('delete-profile', [BiometricProfileController::class, 'destroy']);
});
