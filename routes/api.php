<?php

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

Route::post('register', 'Api\AuthController@register');
Route::post('login', 'Api\AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Api\AuthController@logout');

    // Posts
    Route::get('post/following', 'Api\PostController@showFollowingsPost');
    Route::get('post/explore', 'Api\PostController@explorePost');
    Route::get('post/user/{id}', 'Api\PostController@getPostByUserId');
    Route::get('post/{id}', 'Api\PostController@showPost');
    Route::post('post', 'Api\PostController@createPost');
    Route::put('post/{id}', 'Api\PostController@editPost');
    Route::delete('post/{id}', 'Api\PostController@deletePost');

    // Follow
    Route::get('follow/followings/{id}', 'Api\FollowController@getFollowing');
    Route::get('follow/followers/{id}', 'Api\FollowController@getFollower');
    Route::post('follow/{id}', 'Api\FollowController@follow');
    Route::delete('follow/{id}', 'Api\FollowController@unfollow');

    // Comment
    Route::get('comment/{id_post}', 'Api\CommentController@getCommentsByPost');
    Route::post('comment/{id_post}', 'Api\CommentController@createComment');
    Route::put('comment/{id}', 'Api\CommentController@editComment');
    Route::delete('comment/{id}', 'Api\CommentController@deleteComment');

    // Like
    Route::get('like/{id_post}', 'Api\LikeController@getLikesByPost');
    Route::post('like/{id_post}', 'Api\LikeController@like');
    Route::delete('like/{id_post}', 'Api\LikeController@unlike');

    // User Profile
    Route::post('user/pp', 'Api\UserController@changeProfilePicture');
    Route::get('user/{id}', 'Api\UserController@show');
    Route::get('user', 'Api\UserController@getCurrentLoggedInUser');
    Route::put('user', 'Api\UserController@edit');
    Route::delete('user', 'Api\UserController@delete');
    Route::post('user/find', 'Api\UserController@find');

    // Marketplace
    Route::get('marketplace/all', 'Api\MarketplaceController@index');
    Route::get('marketplace/user/{id}', 'Api\MarketplaceController@getMarketplaceByUserId');
    Route::get('marketplace/user', 'Api\MarketplaceController@getCurrentLoggedInUserMarketplace');
    Route::get('marketplace/{id}', 'Api\MarketplaceController@show');
    Route::post('marketplace', 'Api\MarketplaceController@store');
    Route::put('marketplace/{id}', 'Api\MarketplaceController@update');
    Route::delete('marketplace/{id}', 'Api\MarketplaceController@destroy');
});