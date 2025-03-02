<?php

use App\Http\Controllers\api\alamat\DesaController as AlamatDesaController;
use App\Http\Controllers\api\alamat\KabupatenController as AlamatKabupatenController;
use App\Http\Controllers\api\alamat\KecamatanController as AlamatKecamatanController;
use App\Http\Controllers\api\alamat\ProvinsiController as AlamatProvinsiController;
use App\Http\Controllers\api\AnakasuhController;
use App\Http\Controllers\Api\BerkasController;
use App\Http\Controllers\api\GrupWaliAsuhController;
use App\Http\Controllers\api\KeluargaController;
use App\Http\Controllers\api\WaliasuhController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\SantriController;
use App\Http\Controllers\api\BiodataController;
use App\Http\Controllers\Api\DesaController;
use App\Http\Controllers\Api\EntitasController;
use App\Http\Controllers\Api\GolonganController;
use App\Http\Controllers\Api\JenisBerkasController;
use App\Http\Controllers\api\KabupatenController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\Api\KategoriGolonganController;
use App\Http\Controllers\Api\KecamatanController;
use App\Http\Controllers\api\kewaliasuhan\AnakasuhController as KewaliasuhanAnakasuhController;
use App\Http\Controllers\api\kewaliasuhan\GrupWaliAsuhController as KewaliasuhanGrupWaliAsuhController;
use App\Http\Controllers\api\kewaliasuhan\WaliasuhController as KewaliasuhanWaliasuhController;
use App\Http\Controllers\Api\KhadamController;
use App\Http\Controllers\api\OrangTuaController;
use App\Http\Controllers\Api\Pegawai\BerkasController as PegawaiBerkasController;
use App\Http\Controllers\Api\Pegawai\EntitasController as PegawaiEntitasController;
use App\Http\Controllers\Api\Pegawai\GolonganController as PegawaiGolonganController;
use App\Http\Controllers\Api\Pegawai\JenisBerkasController as PegawaiJenisBerkasController;
use App\Http\Controllers\Api\Pegawai\KaryawanController as PegawaiKaryawanController;
use App\Http\Controllers\Api\Pegawai\KategoriGolonganController as PegawaiKategoriGolonganController;
use App\Http\Controllers\Api\Pegawai\PegawaiController as PegawaiPegawaiController;
use App\Http\Controllers\Api\Pegawai\PengajarController as PegawaiPengajarController;
use App\Http\Controllers\Api\Pegawai\PengurusController as PegawaiPengurusController;
use App\Http\Controllers\Api\Pegawai\WalikelasController as PegawaiWalikelasController;
use App\Http\Controllers\Api\PegawaiController;
use App\Http\Controllers\Api\PelanggaranController;
use App\Http\Controllers\api\pendidikan\JurusanController;
use App\Http\Controllers\api\pendidikan\KelasController;
use App\Http\Controllers\api\pendidikan\LembagaController;
use App\Http\Controllers\api\pendidikan\RombelController;
use App\Http\Controllers\Api\PengajarController;
use App\Http\Controllers\Api\PengurusController;
use App\Http\Controllers\Api\PerizinanController;
use App\Http\Controllers\api\PesertaDidikController;
use App\Http\Controllers\Api\ProvinsiController;
use App\Http\Controllers\api\StatusKeluargaController;
use App\Http\Controllers\Api\WaliKelasController;
use App\Http\Controllers\api\wilayah\BlokController;
use App\Http\Controllers\api\wilayah\DomisiliController;
use App\Http\Controllers\api\wilayah\KamarController;
use App\Http\Controllers\api\wilayah\WilayahController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/santri', SantriController::class);

Route::apiResource('/keluarga',KeluargaController::class);

Route::apiResource('/biodata',BiodataController::class);

Route::apiResource('/status_keluarga',StatusKeluargaController::class);

Route::apiResource('/provinsi',AlamatProvinsiController::class);

Route::apiResource('/kabupaten',AlamatKabupatenController::class);

Route::apiResource('/kecamatan',AlamatKecamatanController::class);

Route::apiResource('/desa',AlamatDesaController::class);

Route::apiResource('/orangtua',OrangTuaController::class);

Route::apiResource('/grupwaliasuh',KewaliasuhanGrupWaliAsuhController::class);

Route::apiResource('/waliasuh',KewaliasuhanWaliasuhController::class);

Route::apiResource('/anakasuh',KewaliasuhanAnakasuhController::class);

Route::apiResource('/wilayah',WilayahController::class);

Route::apiResource('/blok',BlokController::class);

Route::apiResource('/kamar',KamarController::class);

Route::apiResource('/domisili',DomisiliController::class);

Route::apiResource('/khadam', KhadamController::class);

Route::apiResource('/pelanggaran',PelanggaranController::class);

Route::apiResource('/lembaga', LembagaController::class);

Route::apiResource('/jurusan', JurusanController::class);

Route::apiResource('/kelas', KelasController::class);

Route::apiResource('/rombel', RombelController::class);

Route::apiResource('/perizinan', PerizinanController::class);

Route::apiResource('/pegawai',PegawaiPegawaiController::class);

Route::apiResource('/pengajar', PegawaiPengajarController::class);

Route::apiResource('/walikelas',PegawaiWalikelasController::class);

Route::apiResource('/kategorigolongan',PegawaiKategoriGolonganController::class);

Route::apiResource('/golongan',PegawaiGolonganController::class);

Route::apiResource('/entitas',PegawaiEntitasController::class);

Route::apiResource('/pengurus', PegawaiPengurusController::class);

Route::apiResource('/karyawan',PegawaiKaryawanController::class);

Route::apiResource('/jenisberkas',PegawaiJenisBerkasController::class);

Route::apiResource('/berkas',PegawaiBerkasController::class);

Route::get('/listPengajar',[PegawaiPengajarController::class,'Pengajar']);

Route::get('/list-peserta-didik', [PesertaDidikController::class, 'pesertaDidik']);