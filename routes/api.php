<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'user', 'middleware' => 'auth:sanctum'], function () {
    
    Route::get('/', [UserController::class, 'index']);
    
    Route::post('/',[UserController::class, 'update']);

});

Route::get('/token', function (Request $request) {
    $token = $request->session()->token();

    return response()->json([
        'token' => $token,
    ]);
});

require __DIR__.'/auth.php';
