<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use app\Http\Controllers\Api\v1 as V1;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/providers/{provider}/games/import', [V1\GameController::class, 'import']);
Route::get('/games', [V1\GameController::class, 'index']);
