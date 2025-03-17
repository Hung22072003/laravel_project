<?php

use App\Http\Controllers\LinkedinController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\TweetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

# Users 
Route::resource('users', UserController::class);
Route::patch('users/{id}', [UserController::class, 'changePassword']);
Route::get('deleted-users', [UserController::class, 'getDeletedUsers']);
Route::put('deleted-users/{id}', [UserController::class, 'restore']);

# Tweets
Route::resource('tweets', TweetController::class);
Route::get('tweets-interactions/{id}', [TweetController::class, 'tweetInteractions']);

# Posts 
Route::resource('posts', PostController::class);
Route::post('posts/publish', [PostController::class, 'publish']);
Route::get('deleted-posts', [PostController::class, 'getDeletedPosts']);
Route::put('deleted-posts/{id}', [PostController::class, 'restore']);

# Profile
Route::get('profile/me', [UserController::class, 'me']);
Route::get('profile/posts', [PostController::class, 'myPosts']);
Route::get('profile/tweets', [TweetController::class, 'myTweets']);
Route::get('profile/social-accounts', [SocialAccountController::class, 'mySocialAccounts']);

# Social Accounts 
Route::get('social-accounts', [SocialAccountController::class, 'showByUserPlatform']);
Route::delete('social-accounts/{id}', [SocialAccountController::class, 'destroy']);
Route::get('deleted-social-accounts', [SocialAccountController::class, 'getDeletedSocialAccounts']);
Route::put('deleted-social-accounts/{id}', [SocialAccountController::class, 'restore']);

Route::post('/linkedin/post', [LinkedinController::class, 'postToLinkedIn']);