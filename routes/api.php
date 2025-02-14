<?php

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
use App\Http\Controllers\Api\KhadamController;
use App\Http\Controllers\api\OrangTuaController;
use App\Http\Controllers\Api\PegawaiController;
use App\Http\Controllers\Api\PelanggaranController;
use App\Http\Controllers\Api\PengajarController;
use App\Http\Controllers\Api\PengurusController;
use App\Http\Controllers\Api\PerizinanController;
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

Route::apiResource('/provinsi',ProvinsiController::class);

Route::apiResource('/kabupaten',KabupatenController::class);

Route::apiResource('/kecamatan',KecamatanController::class);

Route::apiResource('/desa',DesaController::class);

Route::apiResource('/orangtua',OrangTuaController::class);

Route::apiResource('/grupwaliasuh',GrupWaliAsuhController::class);

Route::apiResource('/waliasuh',WaliasuhController::class);

Route::apiResource('/anakasuh',AnakasuhController::class);

Route::apiResource('/wilayah',WilayahController::class);

Route::apiResource('/blok',BlokController::class);

Route::apiResource('/kamar',KamarController::class);

Route::apiResource('/domisili',DomisiliController::class);

Route::apiResource('/khadam', KhadamController::class);

Route::apiResource('/pelanggaran',PelanggaranController::class);

Route::apiResource('/perizinan', PerizinanController::class);

Route::apiResource('/pegawai',PegawaiController::class);

Route::apiResource('/pengajar', PengajarController::class);

Route::apiResource('/walikelas',WaliKelasController::class);

Route::apiResource('/kategorigolongan',KategoriGolonganController::class);

Route::apiResource('/golongan',GolonganController::class);

Route::apiResource('/entitas',EntitasController::class);

Route::apiResource('/pengurus', PengurusController::class);

Route::apiResource('/karyawan',KaryawanController::class);

Route::apiResource('/jenisberkas',JenisBerkasController::class);

Route::apiResource('/berkas',BerkasController::class);