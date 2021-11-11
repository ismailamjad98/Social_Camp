<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
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
Route::get('emailVerify/{token}/{email}', [UserController::class , 'EmailVerify']);

//User Routes with middleware
Route::middleware(['token'])->group(function () {
    Route::post('/logout', [UserController::class , 'Logout']);
    Route::get('/profile', [UserController::class , 'profile']);
    Route::post('/profile/update/{id}', [UserController::class , 'update']);
});

//POST Routes
Route::middleware(['token'])->group(function () {
    Route::post('/post', [PostController::class , 'create']);
    Route::post('post/update/{id}', [PostController::class , 'update']);
    Route::post('post/myposts', [PostController::class , 'myposts']);
    Route::post('post/allposts', [PostController::class , 'allposts']);
    Route::post('post/delete/{id}', [PostController::class , 'destroy']);
});