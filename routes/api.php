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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//User Routes
Route::post('/register', [UserController::class , 'register']);
Route::post('/login', [UserController::class , 'login']);
Route::get('emailVerify/{token}/{email}', [UserController::class , 'EmailVerify']);

//User Routes with middleware
Route::middleware(['token'])->group(function () {
    //User Routes
    Route::post('/logout', [UserController::class , 'Logout']);
    Route::get('/profile', [UserController::class , 'profile']); 
    Route::post('/profile/update/{id}', [UserController::class , 'update']);
    // Route::post('/profile/delete/{id}', [UserController::class , 'destroy_User']);
    

    //POST Routes
    Route::post('/post', [PostController::class , 'create']);
    Route::post('post/update/{id}', [PostController::class , 'update']);
    Route::get('post/myposts', [PostController::class , 'myposts']); //myemptyposts
    Route::get('post/allposts', [PostController::class , 'allposts']);
    Route::post('post/delete/{id}', [PostController::class , 'destroy']);

    //Send Friend Request Routes
    Route::post('/send_Request', [Friend_Request::class, 'Send_Friend_Request']);
    Route::post('/my_requests', [Friend_Request::class, 'My_Requests']);//myemptyrequests
    Route::post('/receive_request', [Friend_Request::class, 'Receive_Request']);

    //Comments Routes
    Route::post('/comment/{id}' , [CommentController::class, 'create']);
    Route::post('/comment/delete/{id}' , [CommentController::class, 'delete']);
    Route::post('/friend_post/{id}' , [CommentController::class, 'friend_posts']);

});

