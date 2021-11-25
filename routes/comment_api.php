<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Friend_Request;


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

// Routes with middleware
Route::middleware(['token'])->group(function () {

   //Comments Routes
    Route::post('/comment/{id}' , [CommentController::class, 'create']);
    Route::post('/comment/update/{id}' , [CommentController::class, 'update']);
    Route::post('/comment/delete/{id}' , [CommentController::class, 'delete']);
    //comments on friends Post
    Route::post('/friend_post/{id}' , [CommentController::class, 'friend_posts']);

});

