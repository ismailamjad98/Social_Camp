<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//User Routes
Route::post('/register', [UserController::class , 'register']);
Route::post('/login', [UserController::class , 'login']);
// Route::get('/profile', [UserController::class , 'profile']);
// Route::post('/logout', [UserController::class , 'Logout']);

//Routes with middleware
Route::middleware(['token'])->group(function () {
    Route::post('/logout', [UserController::class , 'Logout']);
    Route::get('/profile', [UserController::class , 'profile']);
});

//POST Routes
Route::middleware(['token'])->group(function () {
    Route::post('/post', [PostController::class , 'index']);
    // Route::get('/profile', [UserController::class , 'profile']);
});


