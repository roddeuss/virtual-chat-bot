<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\ChatController;

Route::get('/', [ChatController::class, 'index'])->name('chat.index');
Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/persona/{slug}', [ChatController::class, 'switchPersona'])->name('chat.persona.switch');
