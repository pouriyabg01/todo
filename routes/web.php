<?php

use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Http\Controllers\TelBotController;

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

//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('/', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    $task = \App\Models\Task::find('4');

    dd($user->tasks()->where('tasks.id' , $task->id)->exists());
});
Route::post('telegram' , [TelBotController::class , 'index']);
Route::post('set' , [TelBotController::class , 'set']);

