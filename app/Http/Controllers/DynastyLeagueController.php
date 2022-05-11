<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use PDO;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DynastyTeam;

class DynastyLeagueController extends Controller
{
    public function index(Request $request){
        $input = $request->all();

        return view('dynasty_league_index');
    }

    public function get_teams(Request $request){
        $input = $request->all();
        $answer = array();

        $teams = self::calculate_league_rankings();

        foreach($teams as $team){        
            // Cornerstone players
            $team->cornerstone_players = DynastyTeam::get_cornerstone_players($team->id);

            // Trophies
            $team->trophy_row_array = DynastyTeam::get_team_trophies($team->id);

            // Draft Capital
            $draft_picks = DB::table('dynasty_future_picks')
                ->select(DB::raw('*'))
                ->where('dynasty_future_picks.current_owner_id','=',$team->id)
                ->get();
        }

        $answer['success'] = true;
        $answer['teams'] = $teams;
        return $answer;

        
    }

    public function calculate_league_rankings(){
        $teams = DB::table('dynasty_teams')
            ->select(DB::raw('dynasty_teams.id, dynasty_teams.team_name, dynasty_teams.owner, dynasty_teams.logo'))
            ->where('dynasty_teams.league_id','=',1)
            ->orderBy('owner','asc')
            ->get();

            foreach($teams as $team){
                $raw_value = DB::table('dynasty_team_value_histories')
                    ->select(DB::raw('*'))
                    ->where('dynasty_team_value_histories.dynasty_team_id','=',$team->id)
                    ->orderBy('dynasty_team_value_histories.created_at','desc')
                    ->first();

                $team->value = json_decode($raw_value->value);
                $team->background = json_decode($raw_value->background);
            }

        
        return $teams;
        
    }

}
