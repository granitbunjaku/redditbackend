<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SubredditController;
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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('user', [AuthController::class, 'user']);
    Route::get('subreddit/{name}', [SubredditController::class, 'readSpecificSubreddit']);
    Route::resource('posts', PostController::class);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('subreddit/join/{name}', [SubredditController::class, 'joinSubreddit']);
    Route::resource('subreddit', SubredditController::class);
    Route::resource('categories', CategoriesController::class);
});

Route::get('profilepic/{id}', [AuthController::class, 'profilepic']);
