<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\DeckController;
use App\Http\Controllers\WordController;

Route::get('/hello', function () {
    return 'Hello World';
});

Route::post('/decks', [DeckController::class, 'createForUser'])->middleware('auth:sanctum');
Route::get('/deck/{id}', [DeckController::class, 'show'])->middleware('auth:sanctum');



Route::post('/words', [WordController::class, 'create'])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);

    return ['token' => $token->plainTextToken];
});

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
