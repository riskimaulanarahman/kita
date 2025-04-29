<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::group(['prefix' => 'api'], function () {
    Route::apiResources([
        'datapelanggan' => App\Http\Controllers\Module\DatapelangganController::class,
        'odp' => App\Http\Controllers\Module\OdpController::class,
    ]);
});