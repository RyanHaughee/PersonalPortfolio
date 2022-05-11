<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynastyTeam extends Model
{
    use HasFactory;

    public static function get_team_players($team_id){
        
        $players = DB::table('dynasty_player_teams')
            ->select(DB::raw('dynasty_players.name, dynasty_players.pos, dynasty_players.team_abr, dynasty_player_values.player_value, nfl_logos.logo as team_logo'))
            ->leftJoin('dynasty_players','dynasty_players.id','=','dynasty_player_teams.dynasty_player_id')
            ->leftJoin('dynasty_player_values','dynasty_player_values.dynasty_player_id','=','dynasty_players.id')
            ->join('nfl_logos','nfl_logos.id','=','dynasty_players.team_id')
            ->where('dynasty_player_teams.dynasty_team_id','=',$team_id)
            ->orderBy('dynasty_player_values.player_value','desc')
            ->get();

        return $players;
    }

    public static function get_cornerstone_players($team_id){
        $cornerstone_players = DB::table('dynasty_player_teams')
            ->select(DB::raw('dynasty_players.name, dynasty_players.pos, dynasty_players.team_abr, dynasty_player_values.player_value, nfl_logos.logo as team_logo'))
            ->leftJoin('dynasty_players','dynasty_players.id','=','dynasty_player_teams.dynasty_player_id')
            ->leftJoin('dynasty_player_values','dynasty_player_values.dynasty_player_id','=','dynasty_players.id')
            ->join('nfl_logos','nfl_logos.id','=','dynasty_players.team_id')
            ->where('dynasty_player_teams.dynasty_team_id','=',$team_id)
            ->where('dynasty_player_values.player_value','>=',4800)
            ->orderBy('dynasty_player_values.player_value','desc')
            ->take(5)
            ->get();

        return $cornerstone_players;
    }

    public static function get_team_trophies($team_id){
        $trophies = DB::table('dynasty_trophy_case')
            ->select(DB::raw('dynasty_awards.*, dynasty_trophy_case.year'))
            ->leftJoin('dynasty_awards','dynasty_awards.id','=','dynasty_trophy_case.dynasty_award_id')
            ->where('dynasty_trophy_case.dynasty_team_id','=',$team_id)
            ->orderby('dynasty_awards.significance','desc')
            ->orderby('dynasty_trophy_case.year','desc')
            ->get();

        $trophy_row_array = array();
        $trophy_idx = 0;

        while($trophy_idx < count($trophies)){
            if ($trophy_idx%2 == 0){
                if (!empty($trophy_array)){
                    $trophy_row_array[] = $trophy_array;
                }
                $trophy_array = array();
            }
            $trophy_array[] = $trophies[$trophy_idx];
            $trophy_idx++;
        }
        

        if (!empty($trophy_array)){
            $row_size = count($trophy_array);
            $rows_to_add = 0;
            while($rows_to_add < (2-$row_size)){
                $trophy_array[] = new \stdClass;
                $rows_to_add++;
            }

            $trophy_row_array[] = $trophy_array;
        }

        return $trophy_row_array;
    }
}
