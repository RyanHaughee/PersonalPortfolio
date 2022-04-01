<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DraftController;

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

Route::get('/', function () {
    return view('welcome');
});
 
Route::get('/draft', [DraftController::class, 'index']);
Route::get('get_prospects', [DraftController::class, 'get_prospects']);
Route::get('get_draft_picks', [DraftController::class, 'get_draft_picks']);
Route::get('get_all_draft_picks', [DraftController::class, 'get_all_draft_picks']);
Route::get('get_otc_pick', [DraftController::class, 'get_otc_pick']);
Route::get('get_last_pick', [DraftController::class, 'get_last_pick']);
Route::post('password_check', [DraftController::class, 'password_check']);
Route::post('select_prospect', [DraftController::class, 'select_prospect']);