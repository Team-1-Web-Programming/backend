<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\DonationAnalyticsController;
use App\Http\Controllers\DonationCategoryController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DonationProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('blog')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/{id}', [BlogController::class, 'show'])->name('blog.show');
    Route::group(['middleware' => ['auth:sanctum', 'role:admin']], function () {
        Route::post('/', [BlogController::class, 'store'])->name('blog.store');
        Route::post('/{id}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/{id}', [BlogController::class, 'destroy'])->name('blog.destroy');
    });
});

Route::group(['prefix' => 'user', 'middleware' => 'auth:sanctum'], function () {

    Route::group(['prefix' => 'address'], function () {

        Route::get('/', [AddressController::class, 'index']);
        Route::get('/default', [AddressController::class, 'default']);
        Route::get('/{id}', [AddressController::class, 'detail']);
        Route::post('/', [AddressController::class, 'add']);
        Route::post('/{id}', [AddressController::class, 'edit']);
        Route::delete('/{id}', [AddressController::class, 'delete']);

    });

    Route::group(['prefix' => 'donation'], function () {

        Route::group(['prefix' => 'product'], function () {

            Route::get('/', [DonationProductController::class, 'donor_products']);
            Route::get('/{id}', [DonationProductController::class, 'donor_detail']);
            Route::post('/', [DonationProductController::class, 'add']);
            Route::post('/{id}', [DonationProductController::class, 'edit']);
            Route::delete('/{id}', [DonationProductController::class, 'delete']);

        });

        Route::group(['prefix' => 'analytics'], function () {

            Route::get('/summary', [DonationAnalyticsController::class, 'getSummary'])->name('donation.summary');
            Route::get('/analysis', [DonationAnalyticsController::class, 'getAnalysis'])->name('donation.analysis');
            Route::get('/product-distribution', [DonationAnalyticsController::class, 'getProductDistribution'])->name('donation.productDistribution');
            Route::get('/comparison', [DonationAnalyticsController::class, 'getComparison'])->name('donation.comparison');

        });

        Route::get('/', [DonationController::class, 'index']);
        Route::get('/{id}', [DonationController::class, 'detail']);
        Route::post('/', [DonationController::class, 'add']);
        Route::put('/claim/{donation_product_id}', [DonationController::class, 'claim']);
        Route::put('/status/{donation}', [DonationController::class, 'status']);

    });

    Route::get('/', [UserController::class, 'index']);
    Route::post('/',[UserController::class, 'update']);

});

Route::group(['prefix' => 'donation'], function () {

    Route::group(['prefix' => 'product'], function () {

        Route::get('/', [DonationProductController::class, 'index']);
        Route::get('/{id}', [DonationProductController::class, 'detail']);

    });
    
    Route::group(['prefix' => 'category'], function () {

        Route::get('/', [DonationCategoryController::class, 'index']);

        Route::group(['middleware' => ['auth:sanctum', 'role:admin']], function () {

            Route::post('/', [DonationCategoryController::class, 'add']);
            Route::post('/{id}', [DonationCategoryController::class, 'edit']);
            Route::delete('/{id}', [DonationCategoryController::class, 'delete']);

        });
    });
});

Route::get('/token', function (Request $request) {
    $token = session()->token();

    return response()->json([
        'token' => $token,
    ]);
});

require __DIR__.'/auth.php';
