<?php

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',[UserController::class,'showLoginForm'])->name('login');
Route::post('/login',[UserController::class,'login']);
Route::post('/logout',[UserController::class,'logout'])->name('logout');
Route::get('/register',[UserController::class,'showRegisterForm'])->name('register');
Route::post('/register',[UserController::class,'register']);

Route::middleware(['auth'])->group(function(){
    Route::get('/dashboard',function(){
        return view('auth.dashboard');
    });

    Route::resource('/posts',PostController::class);
    Route::resource('/products',ProductController::class);


    Route::get('/allposts', function () {
        return view('posts.allposts');
    });
    
    Route::get('/addpost', function () {
        return view('posts.addpost');
    });
});

