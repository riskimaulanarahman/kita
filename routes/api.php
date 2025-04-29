<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('getlogin',[App\Http\Controllers\MainController::class, 'getlogin']);
Route::post('upload-berkas/{modname}',[App\Http\Controllers\BerkasController::class, 'update'])->name('uploadberkas');