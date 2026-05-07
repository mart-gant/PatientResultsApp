<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResultsController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'store']);
Route::get('/results', [ResultsController::class, 'show'])->middleware('jwt.auth');
