<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Tournament;
use App\Models\TournamentGame;
use App\Models\TournamentRound;
use App\Models\TournamentTeam;

class ScheduleController extends Controller
{
    public function index(){
        return view('scheduler_index');
    }

    public function generate_bracket(Request $request){
        $input = $request->all();
        $tournament_id = $input['tournament_id'];

        $team_list = DB::table('tournament_teams')
            ->select(DB::raw('tournament_teams.*'))
            ->where('tournament_teams.tournament_id','=',$tournament_id)
            ->get();

        // Number of teams
        $team_num = count($team_list);


        // Number of rounds for bracket creation
        $rounds = ceil(log($team_num)/log(2)-1);
        //Starting team indexes for bracket creation
        $team_idx = [1,2];
        // Function to find the total number of team indexes and put them in the bracket order
        $i = 0;
        while ($i < $rounds){
            $out = array();
            $length = (sizeOf($team_idx)*2+1);
            foreach($team_idx as $p){
                $out[] = $p;
                $out[] = $length-$p;
            }
            $team_idx = $out;
            $i++;
        }

        // Round 1 was already instantiated in the create bracket function above. We need to add 1 back to it.
        $rounds = $rounds+1;

        // Find the max number of teams for the number of rounds.
        $max_teams_in_bracket = pow(2,$rounds);

        // total number of matchups
        $num_of_matchups = ($max_teams_in_bracket/2);

        // Used to identifying what game_id the winner of each matchup will go.
        $winner_matchup = $num_of_matchups - ($max_teams_in_bracket-$team_num);

        // Initiliaze the Games Array to eventually go into the round
        $games_array = array();
        $margin_top = 0;
        $matchup_margin = 10;
        $matchup_height = 30;

        // Create the new round object
        $round = new TournamentRound;
        $round->tournament_id = $tournament_id;
        $round->top_margin = $margin_top;
        $round->matchup_margin = $matchup_margin;
        $round->save();

        // Creating the games
        $team_id = 0;
        $game_id = 1;
        $iteration = 1;
        while($team_id < $max_teams_in_bracket){
            // If iteration can be divided by 2, we need to instantiate a new game.
            if (($iteration % 2) == 1){
                $winner_matchup = $winner_matchup+1;
                $winner_matchup_team_spot = 1;
            } else{
                $winner_matchup_team_spot = 2;
            }

            // The teams array is sorted by matchup. Therefore we can iterate through this array. Index 0 plays index 1, 2 vs 3, 4 vs 5, etc etc.
            $t1_id = $team_idx[$team_id]-1;
            $team_id++;
            $t2_id = $team_idx[$team_id]-1;

            $game = new TournamentGame;
            $game->round_id = $round->id;
            $game->tournament_id = $tournament_id;


            // If the index is greater than the total number of teams, then this represents a matchup with a bye. 
            if ($team_idx[$team_id] > $team_num){
                // Creating a game object [game w/ a bye]
                $game->tournament_game_id = null;
                $game->team_1_id = $team_list[$t1_id]->id;
                $game->team_1_score = null;
                $game->team_1_origin = null;

                $game->team_2_id = null;
                $game->team_2_score = null;
                $game->team_2_origin = null;
                $game->save();

            } else {
                // Creating a game object [regular game]
                $game->tournament_game_id = $game_id;
                $game->winner_to = $winner_matchup;
                $game->winner_to_slot = $winner_matchup_team_spot;

                $game->team_1_id = $team_list[$t1_id]->id;
                $game->team_1_score = null;
                $game->team_1_origin = null;

                $game->team_2_id = $team_list[$t2_id]->id;
                $game->team_2_score = null;
                $game->team_2_origin = null;
                $game->save();
                $game_id++;
            }

            $games_array[] = $game;
            $iteration++;
            $team_id++;
        }

        // Rounds after first. These rounds depend on rounds before them. 
        
        $num_of_matchups = $num_of_matchups / 2;
        // $games_array is as array of the matchups from the first round. The first round is the only round that can have byes, therefore we need to do some special checks to ensure that if a team does not have a first round opponent, they are automatically advanced.
        $first_round = $games_array;
        $past_gid = 1;
        while($num_of_matchups >= 1){
            // Formula to calculate margins for front end view.
            $margin_top = $margin_top + $matchup_height + ($matchup_margin/2);
            $matchup_margin = ($matchup_height*2) + ($matchup_margin*2);

            // Create the new round object
            $round = new TournamentRound;
            $round->tournament_id = $tournament_id;
            $round->top_margin = $margin_top;
            $round->matchup_margin = $matchup_margin;
            $round->save();

            $gid = 0;
            $fr_idx = 0;
            while ($gid < $num_of_matchups){
                if (($iteration % 2) == 1){
                    $winner_matchup = $winner_matchup+1;
                    $winner_matchup_team_spot = 1;
                } else{
                    $winner_matchup_team_spot = 2;
                }

                // Creating a game object
                $game = new TournamentGame;
                $game->tournament_id = $tournament_id;
                $game->round_id = $round->id;
                $game->tournament_game_id = $game_id;
                if ($num_of_matchups != 1){
                    $game->winner_to = $winner_matchup;
                    $game->winner_to_slot = $winner_matchup_team_spot;
                }

                if (!empty($first_round) && !empty($first_round[$fr_idx]->tournament_game_id)){
                    $game->team_1_id = null;
                    $game->team_1_score = null;
                    $game->team_1_origin = $first_round[$fr_idx]->tournament_game_id;
                    $past_gid++;
                    $fr_idx++;
                } else if (!empty($first_round)) {
                    $game->team_1_id = $first_round[$fr_idx]->team_1_id;
                    $game->team_1_score = null;
                    $game->team_1_origin = null;
                    $fr_idx++;
                } else {
                    $game->team_1_id = null;
                    $game->team_1_score = null;
                    $game->team_1_origin = $past_gid;
                    $past_gid++;
                }

                if (!empty($first_round) && !empty($first_round[$fr_idx]->tournament_game_id)){
                    $game->team_2_id = null;
                    $game->team_2_score = null;
                    $game->team_2_origin = $first_round[$fr_idx]->tournament_game_id;
                    $past_gid++;
                    $fr_idx++;
                } else if (!empty($first_round)) {
                    $game->team_2_id = $first_round[$fr_idx]->team_1_id;
                    $game->team_2_score = null;
                    $game->team_2_origin = null;
                    $fr_idx++;
                } else {
                    $game->team_2_id = null;
                    $game->team_2_score = null;
                    $game->team_2_origin = $past_gid;
                    $past_gid++;
                }
                $game->save();
                $games_array[] = $game;
                $game_id++;
                $gid++;
                $iteration++;
            }
            // Once we make it through the iteration once, the past round was no longer the first round. We can set this to be an empty array.
            $first_round = array();
            $num_of_matchups = $num_of_matchups / 2;
        }

        $response['success'] = true;
        return $response;
    }

    public function set_teams(Request $request){
        $input = $request->all();
        $response = array();

        if (empty($input['teams']) || sizeOf($input['teams']) < 3){
            $response['success'] = false;
            $response['message'] = "Not enough teams.";
        }

        // Generate random string for identifying tournament in the future
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 10; $i++) {
            $randstring = $randstring.$characters[rand(0, (strlen($characters)-1))];
        }

        // Initialize a new tournament
        $tournament = new Tournament;
        $tournament->name = "test_12345";
        $tournament->unique_id = $randstring;
        $tournament->tournament_style_id = 1;
        $tournament->save();

        // Add teams to the tournament
        $teams_array = $input['teams'];
        foreach($teams_array as $new_team){
            $team = new TournamentTeam;
            $team->tournament_id = $tournament->id;
            $team->seed = $new_team['seed'];
            $team->name = $new_team['name'];
            $team->save();
        }

        $response['success'] = true;
        $response['tournament_id'] = $tournament->id;
        return $response;
        
    }
    
    public function get_bracket(Request $request){
        $input = $request->all();
        $tournament_id = $input['tournament_id'];
        $tournament = Tournament::where('id','=',$tournament_id);

        $tournament = array();

        $rounds = DB::table('tournament_rounds')
            ->select(DB::raw('tournament_rounds.id, tournament_rounds.top_margin, tournament_rounds.matchup_margin'))
            ->where('tournament_rounds.tournament_id','=',$tournament_id)
            ->get();

        foreach($rounds as $round){
            $games = DB::table('tournament_games')
                ->select(DB::raw('tournament_games.id, tournament_games.tournament_game_id, tournament_team_1.name as team_1, tournament_games.team_1_score, tournament_games.team_1_origin, tournament_team_2.name as team_2, tournament_games.team_2_score, tournament_games.team_2_origin'))
                ->leftJoin('tournament_teams as tournament_team_1','tournament_team_1.id','=','tournament_games.team_1_id')
                ->leftJoin('tournament_teams as tournament_team_2','tournament_team_2.id','=','tournament_games.team_2_id')
                ->where('tournament_games.round_id','=',$round->id)
                ->where('tournament_games.tournament_id','=',$tournament_id)
                ->get();

            $round->games = $games;
            $tournament[] = $round;
        }

        $response['success'] = true;
        $response['tournament'] = $tournament;
        return $response;
    }

    public function submit_score(Request $request){
        $input = $request->all();
        $tournament_id = $input['tournament_id'];
        $tournament = Tournament::find($tournament_id);
        if ($tournament->started != 1){
            $tournament->started = 1;
        }

        $game_id = $input['game_id'];
        $game = TournamentGame::find($game_id);
        $game->team_1_score = $input['team_1_score'];
        $game->team_2_score = $input['team_2_score'];
        $game->save();

        $advancing_team_id = null;
        if ($game->team_1_score > $game->team_2_score){
            $advancing_team_id = $game->team_1_id;
        } else if ($game->team_2_score > $game->team_1_score){
            $advancing_team_id = $game->team_2_id;
        }
        
        if (!empty($game->winner_to) && !empty($advancing_team_id)){
            $new_game_id = DB::table('tournament_games')
                ->select(DB::raw('tournament_games.id'))
                ->where('tournament_games.tournament_game_id','=',$game->winner_to)
                ->where('tournament_games.tournament_id','=',$tournament_id)
                ->first();

            $new_game = TournamentGame::find($new_game_id->id);
            if ($game->winner_to_slot == 1){
                $new_game->team_1_id = $advancing_team_id;
            } elseif ($game->winner_to_slot == 2){
                $new_game->team_2_id = $advancing_team_id;
            }
            $new_game->save();
        }

        $response['success'] = true;
        return $response;
    }

    public function load_bracket_by_code(Request $request){
        $input = $request->all();
        $code = $input['code'];

        $tournament = DB::table('tournaments')
            ->select(DB::raw('*'))
            ->where('tournaments.unique_id','=',$code)
            ->first();

        $response['success'] = true;
        $response['tournament_id'] = $tournament->id;
        return $response;
    }
}
