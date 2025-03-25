<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
    AlumniController
};

use App\Http\Controllers\Api\keluarga\{
    KeluargaController,
    StatusKeluargaController,
    OrangTuaController,
    OrangTuaWaliController
};

use App\Http\Controllers\Api\Alamat\{
    ProvinsiController,
    KabupatenController,
    KecamatanController,
    DesaController
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
    MateriAjarController
};
use App\Models\OrangTuaWali;
use App\Models\Peserta_didik;

// Route untuk autentikasi
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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
    Route::get('{id}/warga-pesantren',[BiodataController::class, 'WargaPesantren']);
    Route::apiResource('/biodata', BiodataController::class);

    // 🏫 Santri & Peserta Didik
    Route::apiResource('/crud/peserta_didik', PesertaDidikController::class);
    Route::apiResource('/crud/pelajar', PelajarController::class);
    Route::apiResource('/crud/santri', SantriController::class);
    Route::get('/peserta-didik', [PesertaDidikController::class, 'getPesertaDidik']);
    Route::get('/peserta-didik/bersaudara', [PesertaDidikController::class, 'bersaudaraKandung']);
    Route::get('/santri', [SantriController::class, 'pesertaDidikSantri']);
    Route::get('/pelajar', [PelajarController::class, 'pesertaDidikPelajar']);
    Route::get('/alumni', [AlumniController::class, 'alumni']);
    Route::get('/peserta-didik/{id}/biodata', [PesertaDidikController::class, 'getBiodata']);
    Route::apiResource('/catatan-afektif',CatatanAfektifController::class);
    Route::apiResource('/catatan-kognitif',CatatanKognitifController::class);

    // 🏫 Keluarga
    Route::apiResource('/crud/keluarga', KeluargaController::class);
    Route::get('/keluarga',[KeluargaController::class,'keluarga']);
    Route::apiResource('/crud/status-keluarga', StatusKeluargaController::class);
    Route::apiResource('/crud/orangtua', OrangTuaWaliController::class);
    Route::get('/orangtua',[OrangTuaWaliController::class,'orangTuaWali']);
    Route::get('/wali', [OrangTuaWaliController::class, 'wali']);

    // 📍 Alamat
    Route::apiResource('/provinsi', ProvinsiController::class);
    Route::apiResource('/kabupaten', KabupatenController::class);
    Route::apiResource('/kecamatan', KecamatanController::class);

    // 🏠 Kewaliasuhan (Asrama/Pengasuhan)
    Route::apiResource('/grup-waliasuh', GrupWaliAsuhController::class);
    Route::apiResource('/waliasuh', WaliasuhController::class);
    Route::apiResource('/anakasuh', AnakasuhController::class);
    Route::get('/list/waliasuh',[WaliasuhController::class,'waliAsuh']);
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
    Route::apiResource('/anakpegawai',AnakPegawaiController::class);
    Route::apiResource('/materiAjar', MateriAjarController::class);
    Route::get('/berkas', [BerkasController::class, 'Berkas']);
    Route::get('/list/pengajars', [PengajarController::class, 'filterPengajar']);
    Route::get('/list/pengurus',[PengurusController::class,'dataPengurus']);
    Route::get('/list/walikelas',[WalikelasController::class,'dataWalikelas']);
    Route::get('list/karyawans',[KaryawanController::class,'dataKaryawan']);
    Route::get('/list/pegawais',[PegawaiController::class,'dataPegawai']);
    Route::get('/list/anakpegawais',[AnakPegawaiController::class,'dataAnakpegawai']);

    // 🚨 Administrasi
    Route::apiResource('/perizinan', PerizinanController::class);
    Route::apiResource('/pelanggaran', PelanggaranController::class);

    // Khadam
    Route::get('/khadam', [KhadamController::class, 'khadam']);
});
Route::get('/catatan-afektif',[CatatanAfektifController::class,'dataCatatanAfektif']);
Route::get('/menu-wilayah',[AnakPegawaiController::class,'menuWilayahBlokKamar']);
Route::get('/menu-negara',[AnakPegawaiController::class,'menuNegaraProvinsiKabupatenKecamatan']);
Route::get('/menu-lembaga',[AnakPegawaiController::class,'menuLembagaJurusanKelasRombel']);
Route::get('/menu-angkatan',[AnakPegawaiController::class,'getAngkatan']);
