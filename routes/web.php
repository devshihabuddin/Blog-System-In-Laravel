<?php

use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubscriberController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;





/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// frontend home controller
Route::get('/', [HomeController::class,'index'])->name('home');

// frontend post_details and all post show controller
Route::get('post/{slug}', [PostController::class, 'postDetails'])->name('post.details');
Route::get('posts', [PostController::class, 'index'])->name('post.index');

//author profile controller
Route::get('profile/{username}', [App\Http\Controllers\AuthorController::class, 'profile'])->name('author.profile');

// category and tag for posts controller
Route::get('category/{slug}', [PostController::class, 'postByCategory'])->name('category.posts');
Route::get('tag/{slug}', [PostController::class, 'postByTag'])->name('tag.posts');


// frontend subscriber controller
Route::post('subscriber', [SubscriberController::class, 'store'])->name('subscriber.store');

// search controller
Route::get('search',[SearchController::class, 'search'])->name('search');

Auth::routes();

// for frontend
Route::group(['middleware' => ['auth']], function(){

    // favorite controller
    Route::post('favorite/{id}/add',[FavoriteController::class, 'add'])->name('post.favorite');

    // comment controller
    Route::post('comment/{post}', [CommentController::class, 'store'])->name('comment.store');

});

// Route group only for admin section
Route::group(['as'=>'admin.', 'prefix'  => 'admin', 'middleware' => ['auth','admin']], function(){

    Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // admin settings controller
    Route::get('gettings', [App\Http\Controllers\Admin\SettingsController::class, 'index' ])->name('settings.index');
    Route::put('profile',  [App\Http\Controllers\Admin\SettingsController::class, 'updateProfile' ])->name('profile.update');
    Route::put('password', [App\Http\Controllers\Admin\SettingsController::class, 'updatePassword' ])->name('password.update');

    //Author controller
    Route::get('authors', [App\Http\Controllers\Admin\AuthorController::class, 'index'])->name('author.index');
    Route::delete('authors/{id}', [App\Http\Controllers\Admin\AuthorController::class, 'destroy'])->name('author.destroy');

    Route::resource('tag', App\Http\Controllers\Admin\TagController::class);
    Route::resource('category', App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('post', App\Http\Controllers\Admin\PostController::class);

    // add another section for pendint post by using same controller PostController
    Route::get('pending/post', [App\Http\Controllers\Admin\PostController::class, 'pending'])->name('post.pending');
    Route::put('post/{id}/approved', [App\Http\Controllers\Admin\PostController::class, 'approved'])->name('post.approved');

    // subscriber controller for get data of databse
    Route::get('subscriber', [App\Http\Controllers\Admin\SubscriberController::class, 'index'])->name('subscriber.index');
    Route::delete('subscribe/{id}', [App\Http\Controllers\Admin\SubscriberController::class, 'destroy'])->name('subscribe.destroy');

    // favorite controller
    Route::get('favorite', [App\Http\Controllers\Admin\FavoriteController::class, 'index'])->name('favorite.index');

    // Comment controller
    Route::get('comments', [App\Http\Controllers\Admin\CommentController::class, 'index'])->name('comment.index');
    Route::delete('comments/{id}', [App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('comment.destroy');


});

// Route group only for author section
Route::group(['as'=>'author.', 'prefix' => 'author', 'middleware' => ['auth','author']], function(){

    Route::get('dashboard', [App\Http\Controllers\Author\DashboardController::class, 'index'])->name('dashboard');

    // author settings controller
    Route::get('gettings', [App\Http\Controllers\Author\SettingsController::class, 'index'])->name('settings.index');
    Route::put('profile',  [App\Http\Controllers\Author\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::put('password', [App\Http\Controllers\Author\SettingsController::class, 'updatePassword'])->name('password.update');



    Route::resource('post', App\Http\Controllers\Author\PostController::class);

    // favorite controller
    Route::get('favorite', [App\Http\Controllers\Author\FavoriteController::class, 'index'])->name('favorite.index');

    // Comment controller
    Route::get('comments', [App\Http\Controllers\Author\CommentController::class, 'index'])->name('comment.index');
    Route::delete('comments/{id}', [App\Http\Controllers\Author\CommentController::class, 'destroy'])->name('comment.destroy');

});


// view composer for frontend footer -category
view()->composer('layouts.frontend.partial.footer', function ($view) {
    $categories = Category::all();
    $view->with('categories',$categories);
});
