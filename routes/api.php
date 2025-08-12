<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('/register-teacher', [TeacherController::class, 'register']);
});
Route::middleware(['auth:api', 'role:admin,teacher'])->group(function () {
    Route::post('/register-student', [StudentController::class, 'register']);
});
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/teachers', [TeacherController::class, 'index']);
});
Route::middleware(['auth:api', 'role:admin,teacher,student'])->group(function () {
    Route::get('/students', [StudentController::class, 'index']);
});
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/teachers/{id}', [TeacherController::class, 'show']);
    Route::put('/teachers/{id}', [TeacherController::class, 'update']);
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy']);
});
Route::middleware(['auth:api', 'role:admin,teacher'])->group(function () {
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::put('/students/{id}', [StudentController::class, 'update']);
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
});


