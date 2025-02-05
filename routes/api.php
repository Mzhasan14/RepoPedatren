<?php

use App\Http\Controllers\api\KeluargaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\SantriController;
use App\Http\Controllers\api\BiodataController;
use App\Http\Controllers\Api\ProvinsiController;
use App\Http\Controllers\api\StatusKeluargaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/santri', SantriController::class);

Route::apiResource('/keluarga',KeluargaController::class);

Route::apiResource('/biodata',BiodataController::class);

Route::apiResource('/status_keluarga',StatusKeluargaController::class);

Route::apiResource('/provinsi',ProvinsiController::class);
