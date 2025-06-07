<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/profile', function(Request $request) {
        return auth()->user();
    });
    Route::get('/getVq', [App\Http\Controllers\Api\VqListingController::class, 'getInitiatorVqListing']);
    Route::get('/createVQ', [App\Http\Controllers\Api\VqListingController::class, 'createVq']);
    Route::get('/getVqDetail', [App\Http\Controllers\Api\VqListingController::class, 'getInitiatorVqDetail']);

});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

