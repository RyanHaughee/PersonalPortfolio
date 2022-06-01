<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\DynastyLeagueController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\RecipeController;

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
 
// DRAFT
// Loading the draft room
Route::get('/draft', [DraftController::class, 'index']);
Route::get('/draft/{id}', [DraftController::class, 'league_index']);
Route::get('/test', [Controller::class, 'test']);


// Functions for the draft room
Route::prefix('draft_function')->group(function () {
    Route::get('/get_prospects', [DraftController::class, 'get_prospects']);
    Route::get('/get_mock_prospects', [DraftController::class, 'get_mock_prospects']);
    Route::get('/get_draft_picks', [DraftController::class, 'get_draft_picks']);
    Route::get('/get_all_draft_picks', [DraftController::class, 'get_all_draft_picks']);
    Route::get('/get_otc_pick', [DraftController::class, 'get_otc_pick']);
    Route::get('/get_last_pick', [DraftController::class, 'get_last_pick']);
    Route::post('/password_check', [DraftController::class, 'password_check']);
    Route::post('/select_prospect', [DraftController::class, 'select_prospect']);
    Route::get('/mock_draft', [DraftController::class, 'mock_draft']);
    Route::get('/get_teams', [DraftController::class, 'get_teams']);
    Route::post('/start_mock', [DraftController::class, 'start_mock']);
    Route::get('/mock_next_pick', [DraftController::class, 'mock_next_pick']);
    Route::get('/mock_until_next_pick', [DraftController::class, 'mock_until_next_pick']);
    Route::get('/load_mock', [DraftController::class, 'get_mock']);
    Route::get('/get_tradeable_draft_picks', [DraftController::class, 'get_tradeable_draft_picks']);
    Route::post('/initiate_trade', [DraftController::class, 'initiate_trade']);
    Route::get('/team_password_check', [DraftController::class, 'team_password_check']);
    Route::get('/get_pending_trades', [DraftController::class, 'get_pending_trades']);
    Route::get('/get_otc_date', [DraftController::class, 'get_otc_date']);
});

// LEAGUE / TOURNAMENT SCHEDULER
Route::get('/scheduler', [ScheduleController::class, 'index']);
Route::prefix('tournament')->group(function () {
    Route::get('/generate_bracket', [ScheduleController::class, 'generate_bracket']);
    Route::get('/get_bracket', [ScheduleController::class, 'get_bracket']);
    Route::post('/set_teams',[ScheduleController::class, 'set_teams']);
    Route::post('/submit_score',[ScheduleController::class, 'submit_score']);
    Route::get('/load_bracket_by_code', [ScheduleController::class, 'load_bracket_by_code']);
    Route::get('/get_tournament_teams', [ScheduleController::class, 'get_tournament_teams']);
});

Route::get('/recipes', [RecipeController::class, 'index']);
Route::prefix('recipe')->group(function () {
    Route::get('/get_random_meal', [RecipeController::class, 'get_random_meal']);
    Route::get('/search_ingredients', [RecipeController::class, 'search_ingredients']);
});

Route::get('/dynasty', [DynastyLeagueController::class, 'index']);
Route::prefix('dynasty_function')->group(function () {
    Route::get('/get_teams', [DynastyLeagueController::class, 'get_teams']);
    Route::get('/get_team_assets', [DynastyLeagueController::class, 'get_team_assets']);
    Route::get('/compute_rankings_change', [DynastyLeagueController::class, 'compute_rankings_change']);
    Route::get('/get_previous_draft', [DynastyLeagueController::class, 'get_previous_draft']);
});
