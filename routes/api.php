<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeckController;
use App\Http\Controllers\WordController;

Route::get('/hello', function () {
    return 'Hello World';
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/decks', [DeckController::class, 'createForUser']);
    Route::get('/deck/{id}', [DeckController::class, 'show']);
    Route::post('/words', [WordController::class, 'create']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
