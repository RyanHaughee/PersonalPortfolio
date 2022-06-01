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
                ->select(DB::raw('dynasty_future_picks.*, dynasty_teams.owner as original_team'))
                ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_future_picks.original_owner_id')
                ->where('dynasty_future_picks.current_owner_id','=',$team->id)
                ->whereNull('dynasty_future_picks.player_id')
                ->orderby('dynasty_future_picks.current_pick_value','desc')
                ->orderby('dynasty_future_picks.round','asc')
                ->orderby('dynasty_future_picks.year','asc')
                ->take(5)
                ->get();

            $team->draft_picks = $draft_picks;

            $previous_ranks = DynastyTeam::get_previous_team_ranks($team->id);

            $team->previous = $previous_ranks;
        }

        $answer['success'] = true;
        $answer['teams'] = $teams;
        return $answer;

        
    }

    public function calculate_league_rankings(){
        $teams = DB::table('dynasty_teams')
            ->select(DB::raw('dynasty_teams.id, dynasty_teams.team_name, dynasty_teams.owner, dynasty_teams.logo'))
            ->where('dynasty_teams.league_id','=',1)
            ->orderBy('team_name','asc')
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

    public function get_team_assets(Request $request){
        $input = $request->all();
        $answer = array();

        if (empty($input) || empty($input['team_id'])){
            $answer['success'] = false;
            $answer['message'] = "Invalid input supplied to get_team_assets";
            return $answer;
        } else{
            $team_id = $input['team_id'];
        }

        $players = DB::table('dynasty_player_teams')
            ->select(DB::raw('dynasty_player_teams.id, dynasty_players.name, dynasty_players.pos, nfl_logos.logo'))
            ->leftJoin('dynasty_players','dynasty_players.id','=','dynasty_player_teams.dynasty_player_id')
            ->leftJoin('nfl_logos','nfl_logos.id','=','dynasty_players.team_id')
            ->where('dynasty_player_teams.dynasty_team_id','=',$team_id)
            ->orderBy('dynasty_players.name','ASC')
            ->get();

        $picks = DB::table('dynasty_future_picks')
            ->select(DB::raw('dynasty_future_picks.id, dynasty_teams.team_name as original_owner, dynasty_future_picks.year, dynasty_future_picks.round'))
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_future_picks.original_owner_id')
            ->where('dynasty_future_picks.current_owner_id','=',$team_id)
            ->whereNull('dynasty_future_picks.player_id')
            ->orderBy('year','asc')
            ->orderBy('round','asc')
            ->orderBy('original_owner','asc')
            ->get();

        $answer['success'] = true;
        $answer['players'] = $players;
        $answer['picks'] = $picks;
        return $answer;
    }

    public function compute_rankings_change(Request $request){
        $input = $request->all();
        $answer = array();
        $team_array = array();

        $team_1_id = $input['team_1_id'];
        $team_2_id = $input['team_2_id'];
        if (!empty($input['team_1_players_sent'])){
            $team_1_sending = $input['team_1_players_sent'];
        } else {
            $team_1_sending = null;
        }
        if (!empty($input['team_2_players_sent'])){
            $team_2_sending = $input['team_2_players_sent'];
        } else {
            $team_2_sending = null;
        }
        if (!empty($input['team_1_picks_sent'])){
            $team_1_sending_picks = $input['team_1_picks_sent'];
        } else {
            $team_1_sending_picks = null;
        }
        if (!empty($input['team_2_picks_sent'])){
            $team_2_sending_picks = $input['team_2_picks_sent'];
        } else {
            $team_2_sending_picks = null;
        }

        // Team 1 without new players
        $team = new \stdClass();
        $team->id = $team_1_id;
        $team->players_to_add = null;
        $team->players_to_receive = null;
        $team->picks_to_add = null;
        $team->picks_to_receive = null;
        $team_array[] = $team;

        // Team 1 with new players
        $team = new \stdClass();
        $team->id = $team_1_id;
        $team->players_to_add = $team_2_sending;
        $team->players_to_receive = $team_1_sending;
        $team->picks_to_add = $team_2_sending_picks;
        $team->picks_to_receive = $team_1_sending_picks;
        $team_array[] = $team;

        // Team 2 without new players
        $team = new \stdClass();
        $team->id = $team_2_id;
        $team->players_to_add = null;
        $team->players_to_receive = null;
        $team->picks_to_add = null;
        $team->picks_to_receive = null;
        $team_array[] = $team;

        // Team 2 with new players
        $team = new \stdClass();
        $team->id = $team_2_id;
        $team->players_to_add = $team_1_sending;
        $team->players_to_receive = $team_2_sending;
        $team->picks_to_add = $team_1_sending_picks;
        $team->picks_to_receive = $team_2_sending_picks;
        $team_array[] = $team;

        $value_array = [];

        foreach($team_array as $team){
            $value = new \stdClass;
            $value->total = 0;
            $value->qb = 0;
            $value->rb = 0;
            $value->wr = 0;
            $value->te = 0;
            $value->dc = 0;
            $value->dc23 = 0;
            $value->dc24 = 0;
            $value->ovr = 0;

            $qb_count = 0;
            $rb_count = 0;
            $wr_count = 0;
            $te_count = 0;

            $where = 'dynasty_player_teams.dynasty_team_id = '. $team->id;
            if (!empty($team->players_to_receive) && sizeof($team->players_to_receive) > 0){
                $where = $where." and dynasty_player_teams.id NOT IN (".implode(', ', $team->players_to_receive).")";
            }
            if (!empty($team->players_to_add) && sizeof($team->players_to_add) > 0){
                $where = $where." or dynasty_player_teams.id IN (".implode(', ', $team->players_to_add).")";
            }
            $players = DB::table('dynasty_player_teams')
                ->select(DB::raw('dynasty_players.name, dynasty_players.pos, dynasty_players.team_abr, dynasty_player_values.player_value, nfl_logos.logo as team_logo'))
                ->leftJoin('dynasty_players','dynasty_players.id','=','dynasty_player_teams.dynasty_player_id')
                ->leftJoin('dynasty_player_values','dynasty_player_values.dynasty_player_id','=','dynasty_players.id')
                ->join('nfl_logos','nfl_logos.id','=','dynasty_players.team_id')
                ->whereRaw($where)
                ->orderBy('dynasty_player_values.player_value','desc')
                ->get();

            foreach($players as $player){
                if ($player->pos == 'QB'){
                    $qb_count++;
                    if ($qb_count <= 2){
                        $value->qb += $player->player_value;
                        $value->total += $player->player_value;
                    } else if ($qb_count <= 4){
                        $value->qb += ($player->player_value * ((5-$qb_count)/3));
                        $value->total += ($player->player_value * ((5-$qb_count)/3));
                    }
                } else if ($player->pos == 'RB'){
                    $rb_count++;
                    if ($rb_count <= 3){
                        $value->rb += $player->player_value;
                        $value->total += $player->player_value;
                    } else if ($rb_count <= 6){
                        $value->rb += ($player->player_value * ((7-$rb_count)/4));
                        $value->total += ($player->player_value * ((7-$rb_count)/4));
                    }
                } else if ($player->pos == 'WR'){
                    $wr_count++;
                    if ($wr_count <= 4){
                        $value->wr += $player->player_value;
                        $value->total += $player->player_value;
                    } else if ($wr_count <= 8){
                        $value->wr += ($player->player_value * ((9-$wr_count)/5));
                        $value->total += ($player->player_value * ((9-$wr_count)/5));
                    }
                } else if ($player->pos == 'TE'){
                    $te_count++;
                    if ($te_count <= 1){
                        $value->te += $player->player_value;
                        $value->total += $player->player_value;
                    } else if ($te_count <= 2){
                        $value->te += ($player->player_value * ((3-$te_count)/2));
                        $value->total += ($player->player_value * ((3-$te_count)/2));
                    }
                }
            }

            $where = 'dynasty_future_picks.player_id IS NOT NULL and dynasty_future_picks.current_owner_id = '.$team->id;
            if (!empty($team->picks_to_receive) && sizeof($team->picks_to_receive) > 0){
                $where = $where." and dynasty_future_picks.id NOT IN (".implode(', ', $team->picks_to_receive).")";
            }
            if (!empty($team->picks_to_add) && sizeof($team->picks_to_add) > 0){
                $where = $where." or dynasty_future_picks.id IN (".implode(', ', $team->picks_to_add).")";
            }
            Log::info($where);

            // Draft Capital
            $draft_picks = DB::table('dynasty_future_picks')
                ->select(DB::raw('*'))
                ->whereRaw($where)
                ->orderBy('dynasty_future_picks.current_pick_value','asc')
                ->get();

            $where = 'dynasty_player_teams.dynasty_team_id = '. $team->id;
            if (!empty($team->players_to_receive) && sizeof($team->players_to_receive) > 0){
                $where = $where." and dynasty_player_teams.id NOT IN (".implode(', ', $team->players_to_receive).")";
            }
            if (!empty($team->players_to_add) && sizeof($team->players_to_add) > 0){
                $where = $where." or dynasty_player_teams.id IN (".implode(', ', $team->players_to_add).")";
            }
            $players = DB::table('dynasty_player_teams')
                ->select(DB::raw('dynasty_players.name, dynasty_players.pos, dynasty_players.team_abr, dynasty_player_values.player_value, nfl_logos.logo as team_logo'))
                ->leftJoin('dynasty_players','dynasty_players.id','=','dynasty_player_teams.dynasty_player_id')
                ->leftJoin('dynasty_player_values','dynasty_player_values.dynasty_player_id','=','dynasty_players.id')
                ->join('nfl_logos','nfl_logos.id','=','dynasty_players.team_id')
                ->whereRaw($where)
                ->orderBy('dynasty_player_values.player_value','asc')
                ->get();

            $dc_to_add = 0;

            $dc23_count = 0;
            $dc24_count = 0;

            $p_index_to_subtract_from = 0;
            $dp_index = 0;
            $p_index = 0;
            $pick_to_add = null;
            $roster_player_num = sizeOf($players);

            if ($roster_player_num > 22){
                $extra_players = $roster_player_num - 22;
                $p_index = $p_index+$extra_players;
                $p_index_to_subtract_from = $p_index_to_subtract_from+$extra_players;
            } else if ($roster_player_num < 22){
                $roster_spots = 22-$roster_player_num;
                while ($roster_spots > 0){
                    Log::info("PICKS VAL: ".$draft_picks[$dp_index]->current_pick_value." .......  TOTAL: ".($draft_picks[$dp_index]->current_pick_value));
                    $dc_to_add += $draft_picks[$dp_index]->current_pick_value;
                    $dp_index++;
                    $roster_spots--;
                }
            }

            while ($dp_index < sizeof($draft_picks)){
                if ($draft_picks[$dp_index]->current_pick_value > $players[$p_index]->player_value){
                    if (empty($pick_to_add)){
                        $pick_to_add = $draft_picks[$dp_index];
                        $dp_index++;
                        $p_index++;
                    } else {
                        Log::info("PLAYER ADDED: ".$players[$p_index_to_subtract_from]->name." ........ PICKS VAL: ".$pick_to_add->current_pick_value." .......  PLAYER VAL: ".$players[$p_index_to_subtract_from]->player_value." ....... TOTAL: ".($pick_to_add->current_pick_value-$players[$p_index_to_subtract_from]->player_value));
                        $dc_to_add += $pick_to_add->current_pick_value-$players[$p_index_to_subtract_from]->player_value;
                        $p_index_to_subtract_from++;
                        $pick_to_add = null;
                    }
                } else {
                    $dp_index++;
                } 
            }

            if (!empty($pick_to_add)){
                Log::info("PLAYER ADDED: ".$players[$p_index_to_subtract_from]->name." ........ PICKS VAL: ".$pick_to_add->current_pick_value." .......  PLAYER VAL: ".$players[$p_index_to_subtract_from]->player_value." ....... TOTAL: ".($pick_to_add->current_pick_value-$players[$p_index_to_subtract_from]->player_value));
                $dc_to_add += $pick_to_add->current_pick_value-$players[$p_index_to_subtract_from]->player_value;
                $p_index_to_subtract_from++;
                $pick_to_add = null;
            }

            $reverse_dp_index = sizeof($draft_picks) - 1;

            while($reverse_dp_index >= 0){
                if ($draft_picks[$reverse_dp_index]->year == 2023){
                    if ($dc23_count < 6){
                        $value->dc23 += $draft_picks[$reverse_dp_index]->current_pick_value * (1.5-($dc23_count*0.25));
                    }
                    $dc23_count++;
                } else {
                    if ($dc24_count < 6){
                        $value->dc24 += $draft_picks[$reverse_dp_index]->current_pick_value * (1.5-($dc24_count*0.25));
                    }
                    $dc24_count++;
                }
                $reverse_dp_index--;
            }

            $value->dc = $value->dc23 + $value->dc24;

            // OVERALL 
            $value->ovr = $value->total + $dc_to_add;

            Log::info($dc_to_add);

            $value_array[] = $value;
            $team->value = $value;

        }

        $change_array = array();
        $max_obj_raw = DB::table('dynasty_team_value_histories')
            ->select(DB::raw('*'))
            ->where('dynasty_team_value_histories.max_obj','=','1')
            ->orderBy('dynasty_team_value_histories.created_at','desc')
            ->first();

        $max_obj = json_decode($max_obj_raw->value);

        
        $change = new \stdClass();
        // Ovr
        $change->ovr = number_format((($value_array[1]->ovr - $value_array[0]->ovr)/$max_obj->ovr)*100,2);

        // Total
        $change->total = number_format((($value_array[1]->total - $value_array[0]->total)/$max_obj->total)*100,2);

        // QB
        $change->qb = number_format((($value_array[1]->qb - $value_array[0]->qb)/$max_obj->qb)*100,2);

        // RB
        $change->rb = number_format((($value_array[1]->rb - $value_array[0]->rb)/$max_obj->rb)*100,2);

        // WR
        $change->wr = number_format((($value_array[1]->wr - $value_array[0]->wr)/$max_obj->wr)*100,2);

        // TE 
        $change->te = number_format((($value_array[1]->te - $value_array[0]->te)/$max_obj->te)*100,2);

        // DC
        $change->dc = number_format((($value_array[1]->dc - $value_array[0]->dc)/$max_obj->dc)*100,2);

        // DC23
        $change->dc23 = number_format((($value_array[1]->dc23 - $value_array[0]->dc23)/$max_obj->dc23)*100,2);

        // DC24
        $change->dc24 = number_format((($value_array[1]->dc24 - $value_array[0]->dc24)/$max_obj->dc24)*100,2);

        $team_1_change = $change; 
        $change_array[] = $team_1_change;

        $change = new \stdClass();
        // Ovr
        $change->ovr = number_format((($value_array[3]->ovr - $value_array[2]->ovr)/$max_obj->ovr)*100,2);

        // Total
        $change->total = number_format((($value_array[3]->total - $value_array[2]->total)/$max_obj->total)*100,2);

        // QB
        $change->qb = number_format((($value_array[3]->qb - $value_array[2]->qb)/$max_obj->qb)*100,2);

        // RB
        $change->rb = number_format((($value_array[3]->rb - $value_array[2]->rb)/$max_obj->rb)*100,2);

        // WR
        $change->wr = number_format((($value_array[3]->wr - $value_array[2]->wr)/$max_obj->wr)*100,2);

        // TE 
        $change->te = number_format((($value_array[3]->te - $value_array[2]->te)/$max_obj->te)*100,2);

        // DC
        $change->dc = number_format((($value_array[3]->dc - $value_array[2]->dc)/$max_obj->dc)*100,2);

        // DC23
        $change->dc23 = number_format((($value_array[3]->dc23 - $value_array[2]->dc23)/$max_obj->dc23)*100,2);

        // DC24
        $change->dc24 = number_format((($value_array[3]->dc24 - $value_array[2]->dc24)/$max_obj->dc24)*100,2);

        $team_2_change = $change; 
        $change_array[] = $team_2_change;

        $answer['success'] = true;
        $answer['change_array'] = $change_array;
        return $answer;

        
        
    }

    public function get_previous_draft(Request $request){
        $input = $request->all();
        $answer = array();

        if (empty($input) || empty($input['year'])){
            $answer['success'] = false;
            $answer['message'] = "Improper input provided to the Controller.";
            return $answer;
        } else {
            $year = $input['year'];
        }

        $picks = DB::table('dynasty_future_picks')
            ->select(DB::raw('dynasty_future_picks.round, dynasty_future_picks.pick_num, dynasty_players.*, dynasty_teams.*'))
            ->leftJoin('dynasty_players','dynasty_players.id','=','dynasty_future_picks.player_id')
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_future_picks.current_owner_id')
            ->where('dynasty_future_picks.year','=',$year)
            ->orderBy('dynasty_future_picks.round','asc')
            ->orderBy('dynasty_future_picks.pick_num','asc')
            ->get();

        $answer['success'] = true;
        $answer['picks'] = $picks;
        return $answer; 

    }

}
