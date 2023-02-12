<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\PhotosController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubredditController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VotesController;
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
    Route::resource('posts', PostController::class);
    Route::resource('comments', CommentsController::class);
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('subreddit/join/{name}', [SubredditController::class, 'joinSubreddit']);
    Route::resource('subreddits', SubredditController::class);
    Route::get('subreddit/{name}/posts', [SubredditController::class, 'subredditPosts']);
    Route::post('vote/{name}/{type}/{id}', [VotesController::class, 'vote']);
    Route::post('update/{name}/pic/{id}', [PhotosController::class, 'updatepic']);
    Route::resource('categories', CategoriesController::class)->only('index');
    Route::get('communities/posts', [PostController::class, 'getCommunitiesPosts']);
    Route::group(['middleware' => ['role:admin']], function () {
        Route::resource('categories', CategoriesController::class)->only(['store', 'update', 'destroy']);;
    });
});

Route::put('verifyemail/{id}', [UserController::class, 'verifyEmail']);
Route::get('user/{name}/posts', [UserController::class, 'userPosts']);
Route::get('user/{id}', [UserController::class, 'getUserById']);
Route::resource('subreddits', SubredditController::class)->only('index');
Route::get('all/tredingposts', [PostController::class, 'getTrendingPosts']);
Route::get('search/{name}', [SearchController::class, 'findByName']);
Route::get('post/{id}', [PostController::class, 'showPost']);
Route::get('comment/{id}', [CommentsController::class, 'showComment']);
Route::get('show/subreddit/{name}', [SubredditController::class, 'showSubNotAuth']);
Route::get('subreddit/{name}/posts/show', [SubredditController::class, 'subredditPostsNotAuth']);
Route::get('all/posts', [PostController::class, 'showAllPosts']);
Route::get('photos/{filename}', [PhotosController::class, 'getPic']);
Route::get('postfile/{filename}', [PostController::class, 'returnFile']);
