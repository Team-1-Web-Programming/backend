<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'user', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', function (Request $request) {
        return $request->user();
    });
});

Route::get('/token', function (Request $request) {
    $token = $request->session()->token();

    return response()->json([
        'token' => $token,
    ]);
});

require __DIR__.'/auth.php';
