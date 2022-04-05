<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DynastyPick;
use App\Models\TeamNeed;
use App\Models\MockDraft;
use App\Models\MockDraftPick;

class DraftController extends Controller
{
    public function index(Request $request){
        $input = $request->all();
        if (!empty($input['mode']) && $input['mode'] == 'dark'){
            return view('draft_index_dark');
        } else {
            return view('draft_index');
        }
    }

    public function get_prospects(Request $request){
        $input = $request->all();

        $where_statement = "prospects.pos_rank is not null";

        if (!empty($input) && !empty($input['pos']) && $input['pos'] != 'All'){
            $where_statement = $where_statement . " and prospects.pos = '" . $input['pos'] . "'";
            $order_by = "prospects.pos_rank";
        } else {
            $order_by = "prospects.ovr_rank";
        }

        $prospects = DB::table('prospects')
            ->select(DB::raw('prospects.*, cfb_team_logos.dark_logo as cfb_team_logo, nfl_logos.logo as nfl_team_logo, dynasty_picks.id as pick_id, dynasty_teams.logo as ff_logo, dynasty_picks.round, dynasty_picks.pick'))
            ->leftJoin('cfb_team_logos','cfb_team_logos.team_api_id','=','prospects.school_id')
            ->leftJoin('nfl_logos','nfl_logos.id','=','prospects.team_id')
            ->leftJoin('dynasty_picks','dynasty_picks.prospect_id','=','prospects.id')
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
            ->whereRaw($where_statement)
            ->orderBy($order_by,'asc')
            ->get();

        $today = strtotime(date('Y-m-d'));
        foreach($prospects as $prospect){
            $bday = strtotime($prospect->birthday);
            $age = round(($today-$bday) / (365*60*60*24),1);
            $prospect->age = $age;

            // Setting signficant stats and headers
            $prospect->headers = [];
            $prospect->stat_stats = [];
            if ($prospect->pos == "WR" || $prospect->pos == "TE"){
                $prospect->stat_headers = ["GP","REC","YD","TD"];
                $prospect->stat_stats = [$prospect->gp, $prospect->rec, $prospect->rec_yds, $prospect->rec_td];
            } else if ($prospect->pos == "QB"){
                $prospect->stat_headers = ["GP","CMP","ATT","YD","TD","INT"];
                $prospect->stat_stats = [$prospect->gp, $prospect->pass_comp, $prospect->pass_att, $prospect->pass_yd, $prospect->pass_td, $prospect->pass_int];
            } else if ($prospect->pos == "RB"){
                $prospect->stat_headers = ["GP","RSH","YD","TD","REC","YD","TD"];
                $prospect->stat_stats = [$prospect->gp, $prospect->rush_att, $prospect->rush_yd, $prospect->rush_td, $prospect->rec, $prospect->rec_yds, $prospect->rec_td];
            }

        }

        $response = array();
        $response['prospects'] = $prospects;
        return $response;
    }

    public function get_mock_prospects(Request $request){
        $input = $request->all();

        if (empty($input['mock_draft_id'])){
            $response = array();
            $response['success'] = false;
            $response['message'] = "No mock draft id provided.";
            return $response;
        } else {
            $mock_draft_id = intval($input['mock_draft_id']);
        }

        $where_statement = "prospects.pos_rank is not null";

        if (!empty($input) && !empty($input['pos']) && $input['pos'] != 'All'){
            $where_statement = $where_statement . " and prospects.pos = '" . $input['pos'] . "'";
            $order_by = "prospects.pos_rank";
        } else {
            $order_by = "prospects.ovr_rank";
        }

        $prospects = DB::table('prospects')
            ->select(DB::raw('prospects.*, cfb_team_logos.dark_logo as cfb_team_logo, nfl_logos.logo as nfl_team_logo, mock_draft_picks.id as pick_id, dynasty_teams.logo as ff_logo, dynasty_picks.round, dynasty_picks.pick'))
            ->leftJoin('cfb_team_logos','cfb_team_logos.team_api_id','=','prospects.school_id')
            ->leftJoin('nfl_logos','nfl_logos.id','=','prospects.team_id')
            ->leftJoin('mock_draft_picks', function($join) use($mock_draft_id){
                $join->on('mock_draft_picks.prospect_id','=','prospects.id');
                $join->where('mock_draft_picks.mock_draft_id','=',$mock_draft_id);
            })
            ->leftJoin('dynasty_picks','dynasty_picks.id','=','mock_draft_picks.dynasty_pick_id')
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','mock_draft_picks.team_id')
            ->whereRaw($where_statement)
            ->orderBy($order_by,'asc')
            ->get();

        $today = strtotime(date('Y-m-d'));
        foreach($prospects as $prospect){
            $bday = strtotime($prospect->birthday);
            $age = round(($today-$bday) / (365*60*60*24),1);
            $prospect->age = $age;

            // Setting signficant stats and headers
            $prospect->headers = [];
            $prospect->stat_stats = [];
            if ($prospect->pos == "WR" || $prospect->pos == "TE"){
                $prospect->stat_headers = ["GP","REC","YD","TD"];
                $prospect->stat_stats = [$prospect->gp, $prospect->rec, $prospect->rec_yds, $prospect->rec_td];
            } else if ($prospect->pos == "QB"){
                $prospect->stat_headers = ["GP","CMP","ATT","YD","TD","INT"];
                $prospect->stat_stats = [$prospect->gp, $prospect->pass_comp, $prospect->pass_att, $prospect->pass_yd, $prospect->pass_td, $prospect->pass_int];
            } else if ($prospect->pos == "RB"){
                $prospect->stat_headers = ["GP","RSH","YD","TD","REC","YD","TD"];
                $prospect->stat_stats = [$prospect->gp, $prospect->rush_att, $prospect->rush_yd, $prospect->rush_td, $prospect->rec, $prospect->rec_yds, $prospect->rec_td];
            }

        }

        $response = array();
        $response['prospects'] = $prospects;
        return $response;
    }

    public function get_draft_picks(Request $request){
        $input = $request->all();

        $draft_picks = DB::table('dynasty_picks')
            ->select(DB::raw('dynasty_picks.id, prospects.name as prospect_name, prospects.pos as position, dynasty_teams.team_name as team_name, dynasty_picks.round, dynasty_picks.pick, cfb_teams.school'))
            ->leftJoin('prospects','prospects.id','=','dynasty_picks.prospect_id')
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
            ->leftJoin('cfb_teams','cfb_teams.api_id','=','prospects.school_id')
            ->whereNotNull('dynasty_picks.prospect_id')
            ->orderBy('dynasty_picks.round','ASC')
            ->orderBy('dynasty_picks.pick','ASC')
            ->get();

        $all_draft_picks = DB::table('dynasty_picks')
            ->select(DB::raw('dynasty_picks.id, prospects.name as prospect_name, prospects.pos as position, dynasty_teams.team_name as team_name, dynasty_picks.round, dynasty_picks.pick, cfb_teams.school, dynasty_teams.logo, prospects.image as prospect_image, cfb_team_logos.dark_logo as cfb_team_logo, nfl_logos.logo as nfl_team_logo'))
            ->leftJoin('prospects','prospects.id','=','dynasty_picks.prospect_id')
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
            ->leftJoin('cfb_teams','cfb_teams.api_id','=','prospects.school_id')
            ->leftJoin('cfb_team_logos','cfb_team_logos.team_api_id','=','prospects.school_id')
            ->leftJoin('nfl_logos','nfl_logos.id','=','prospects.team_id')
            ->orderBy('dynasty_picks.round','ASC')
            ->orderBy('dynasty_picks.pick','ASC')
            ->get();

        $otc_pick = DynastyPick::find_otc_pick();

        $response = array();
        $response['draft_picks'] = $draft_picks;
        $response['otc_pick'] = $otc_pick;
        return $response;
    }

    public function get_all_draft_picks(Request $request){
        $input = $request->all();

        $mock_draft_id = null;
        if (!empty($input) && !empty($input['mock_draft_id'])){
            $mock_draft_id = $input['mock_draft_id'];
        }

        if (!empty($mock_draft_id)){
            $all_draft_picks = DB::table('dynasty_picks')
                ->select(DB::raw('dynasty_picks.id, prospects.name as prospect_name, prospects.pos as position, dynasty_teams.team_name as team_name, dynasty_picks.round, dynasty_picks.pick, cfb_teams.school, dynasty_teams.logo, prospects.image as prospect_image, cfb_team_logos.dark_logo as cfb_team_logo, nfl_logos.logo as nfl_team_logo'))
                ->leftJoin('mock_draft_picks', function($join) use($mock_draft_id){
                    $join->on('mock_draft_picks.dynasty_pick_id','=','dynasty_picks.id');
                    $join->where('mock_draft_picks.mock_draft_id','=',$mock_draft_id);
                })
                ->leftJoin('prospects','prospects.id','=','mock_draft_picks.prospect_id')
                ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
                ->leftJoin('cfb_teams','cfb_teams.api_id','=','prospects.school_id')
                ->leftJoin('cfb_team_logos','cfb_team_logos.team_api_id','=','prospects.school_id')
                ->leftJoin('nfl_logos','nfl_logos.id','=','prospects.team_id')
                ->orderBy('dynasty_picks.round','ASC')
                ->orderBy('dynasty_picks.pick','ASC')
                ->get();
        } else {
            $all_draft_picks = DB::table('dynasty_picks')
                ->select(DB::raw('dynasty_picks.id, prospects.name as prospect_name, prospects.pos as position, dynasty_teams.team_name as team_name, dynasty_picks.round, dynasty_picks.pick, cfb_teams.school, dynasty_teams.logo, prospects.image as prospect_image, cfb_team_logos.dark_logo as cfb_team_logo, nfl_logos.logo as nfl_team_logo'))
                ->leftJoin('prospects','prospects.id','=','dynasty_picks.prospect_id')
                ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
                ->leftJoin('cfb_teams','cfb_teams.api_id','=','prospects.school_id')
                ->leftJoin('cfb_team_logos','cfb_team_logos.team_api_id','=','prospects.school_id')
                ->leftJoin('nfl_logos','nfl_logos.id','=','prospects.team_id')
                ->orderBy('dynasty_picks.round','ASC')
                ->orderBy('dynasty_picks.pick','ASC')
                ->get();
        }

        $otc_determined = false;
        foreach($all_draft_picks as $pick){
            $pick->otc = false;
            if (empty($pick->prospect_name) && !$otc_determined){
                $otc_determined = true;
                $pick->otc = true;
            }
        }

        $response = array();
        $response['all_draft_picks'] = $all_draft_picks;
        return $response;
    }

    public function get_otc_pick(Request $request){
        $input = $request->all();

        if (!empty($input['mock_draft_id'])){
            $otc_pick = MockDraftPick::find_otc_pick($input['mock_draft_id']);
        } else {
            $otc_pick = DynastyPick::find_otc_pick();
        }

        $response = array();
        $response['otc_pick'] = $otc_pick;
        return $response;
    }

    public function get_last_pick(Request $request){
        $input = $request->all();

        if (!empty($input['mock_draft_id'])){
            $last_pick = MockDraftPick::get_last_pick($input['mock_draft_id']);
        } else {
            $last_pick = DynastyPick::get_last_pick();
        }

        $response = array();
        $response['last_pick'] = $last_pick;
        return $response;
    }

    public function password_check(Request $request){
        $input = $request->all();

        if (!empty($input) && !empty($input['password'])){
            $password_submitted = $input['password'];
        }
        
        $otc_pick = DynastyPick::find_otc_pick();

        if ($password_submitted == $otc_pick->password){
            $response = array();
            $response['success'] = true;
            return $response;
        } else {
            $response = array();
            $response['success'] = false;
            $response['message'] = "Password incorrect.";
            return $response;
        }
    }

    public function select_prospect(Request $request){
        $input = $request->all();

        if (empty($input) || (empty($input['prospect_id']))){
            $response = array();
            $response['success'] = false;
            $response['message'] = "No input provided.";
            return $response;
        }

        if (!empty($input['mock_draft_id'])){
            $otc_pick = MockDraftPick::find_otc_pick($input['mock_draft_id']);
            $pick = new MockDraftPick;
            $pick->mock_draft_id = $input['mock_draft_id'];
            $pick->prospect_id = $input['prospect_id'];
            $pick->team_id = $otc_pick->team_id;
            $pick->dynasty_pick_id = $otc_pick->id;
            $pick->user_pick = 1;
            $pick->save();
        } else {
            $otc_pick = DynastyPick::find_otc_pick();
            $pick = DynastyPick::find($otc_pick->id);
            $pick->prospect_id = $input['prospect_id'];
            $pick->save();
        }

        $response = array();
        $response['success'] = true;
        return $response;
    }

    public function mock_draft(){
        // $input = $request->all();
        $team_id = 0;
        $pick = 1;
        $answer = array();

        while($pick <= 38){
            $otc_pick = DynastyPick::find_otc_pick();
            if ($otc_pick->team_id === $team_id){
                $answer['success'] = true;
                return;
            } else {
                $pick_string = "pick_".$pick;

                $eligible_players = DB::table('mock_draft_data')
                    ->select(DB::raw('mock_draft_data.'.$pick_string.' as prospect_score, prospects.pos, prospects.id as prospect_id'))
                    ->leftJoin('prospects','prospects.id','=','mock_draft_data.prospect_id')
                    ->leftJoin('dynasty_picks','dynasty_picks.prospect_id','=','prospects.id')
                    ->whereRaw('mock_draft_data.'.$pick_string.' > 0 and dynasty_picks.id is null')
                    ->get();

                $team_needs = DB::table('team_needs')
                    ->select(DB::raw('team_needs.*'))
                    ->where('team_needs.team_id','=',$otc_pick->team_id)
                    ->first();

                $total_pick_val = 0;
                foreach($eligible_players as $player){
                    $team_need_pos_multiplier = (($team_needs->{strtolower($player->pos)})+2)/3;
                    $player->new_player_score = $player->prospect_score * $team_need_pos_multiplier;
                    $total_pick_val = $total_pick_val + $player->new_player_score;
                }

                $random_number = rand(0, $total_pick_val);
                $pick_check_int = $random_number;
                $selected_player = null;

                foreach($eligible_players as $player){
                    $pick_check_int = $pick_check_int - $player->new_player_score;
                    if ($pick_check_int <= 0){
                        $selected_player = $player;
                        break;
                    }
                }
                $update_pick = DynastyPick::find($pick);
                $update_pick->prospect_id = $selected_player->prospect_id;
                $update_pick->save();

                $team_need = TeamNeed::find($team_needs->id);
                if ($player->pos ==  "QB" || $player->pos == "TE" && $team_need->{strtolower($player->pos)} > 2){
                    $team_need->{strtolower($player->pos)} = $team_need->{strtolower($player->pos)} - 2;
                } else if ($player->pos ==  "QB" || $player->pos == "TE"){
                    $team_need->{strtolower($player->pos)} = 1;
                } else if($team_need->{strtolower($player->pos)} > 1) {
                    $team_need->{strtolower($player->pos)} = $team_need->{strtolower($player->pos)} - 1;
                }
                $team_need->save();
                
            }

            $pick++;
        }
        return;

    }

    public function get_teams(){
        $answer = array();
        $teams = DB::table('dynasty_teams')
            ->select(DB::raw('dynasty_teams.*, team_needs.qb, team_needs.rb, team_needs.wr, team_needs.te'))
            ->leftJoin('team_needs','team_needs.team_id','=','dynasty_teams.id')
            ->get();

        if (empty($teams)){
            $answer['success'] = false;
            $answer['message'] = "No teams found.";
        }

        $answer['success'] = true;
        $answer['teams'] = $teams;
        return $answer;
    }

    public function start_mock(){
        $answer = array();
        $mock_draft = new MockDraft;
        $mock_draft->save();

        $answer['success'] = true;
        $answer['mock_draft_id'] = $mock_draft->id;
        return $answer;
    }

    public function mock_next_pick(Request $request){
        $input = $request->all();

        if (empty($input['team_id']) || empty($input['mock_draft_id'])){
            $answer = array();
            $answer['success'] = false;
            $answer['message'] = "No team_id or mock_draft_id provided to the controller.";
        } else {
            $team_id = $input['team_id'];
            $mock_draft_id = $input['mock_draft_id'];
        }

        $otc_pick = MockDraftPick::find_otc_pick($mock_draft_id);

        if ($otc_pick->team_id == $team_id){
            $answer = array();
            $answer['success'] = false;
            $answer['message'] = "You are the team on the clock!";
            return $answer;
        }

        $pick_string = "pick_".$otc_pick->id;

        $eligible_players = DB::table('mock_draft_data')
            ->select(DB::raw('mock_draft_data.'.$pick_string.' as prospect_score, prospects.pos, prospects.id as prospect_id'))
            ->leftJoin('prospects','prospects.id','=','mock_draft_data.prospect_id')
            ->leftJoin('mock_draft_picks', function($join) use($mock_draft_id){
                $join->on('mock_draft_picks.prospect_id','=','prospects.id');
                $join->where('mock_draft_picks.mock_draft_id','=',$mock_draft_id);
            })
            ->whereRaw('mock_draft_data.'.$pick_string.' > 0 and mock_draft_picks.id is null')
            ->get();

        $team_needs = DB::table('team_needs')
            ->select(DB::raw('team_needs.*'))
            ->where('team_needs.team_id','=',$otc_pick->team_id)
            ->first();

        $total_pick_val = 0;
        foreach($eligible_players as $player){
            $team_need_pos_multiplier = (($team_needs->{strtolower($player->pos)})+2)/3;
            $player->new_player_score = $player->prospect_score * $team_need_pos_multiplier;
            $total_pick_val = $total_pick_val + $player->new_player_score;
        }

        $random_number = rand(0, $total_pick_val);
        $pick_check_int = $random_number;
        $selected_player = null;

        foreach($eligible_players as $player){
            $pick_check_int = $pick_check_int - $player->new_player_score;
            if ($pick_check_int <= 0){
                $selected_player = $player;
                break;
            }
        }

        $update_pick = new MockDraftPick;
        $update_pick->mock_draft_id = $mock_draft_id;
        $update_pick->prospect_id = $selected_player->prospect_id;
        $update_pick->team_id = $otc_pick->team_id;
        $update_pick->dynasty_pick_id = $otc_pick->id;
        $update_pick->user_pick = 0;
        $update_pick->save();

        $answer = array();
        $answer['success'] = true;
        return $answer;
        
    }

    public function mock_until_next_pick(Request $request){
        $input = $request->all();

        if (empty($input['team_id']) || empty($input['mock_draft_id'])){
            $answer = array();
            $answer['success'] = false;
            $answer['message'] = "No team_id or mock_draft_id provided to the controller.";
        } else {
            $team_id = $input['team_id'];
            $mock_draft_id = $input['mock_draft_id'];
        }

        $otc_pick = MockDraftPick::find_otc_pick($mock_draft_id);
        $pick = $otc_pick->id;

        while($pick <= 38){
            $otc_pick = MockDraftPick::find_otc_pick($mock_draft_id);

            Log::info($otc_pick->team_id);
            Log::info($team_id);
            if ($otc_pick->team_id == $team_id){
                Log::info("break");
                break;
            }

            $pick_string = "pick_".$otc_pick->id;

            $eligible_players = DB::table('mock_draft_data')
                ->select(DB::raw('mock_draft_data.'.$pick_string.' as prospect_score, prospects.pos, prospects.id as prospect_id'))
                ->leftJoin('prospects','prospects.id','=','mock_draft_data.prospect_id')
                ->leftJoin('mock_draft_picks', function($join) use($mock_draft_id){
                    $join->on('mock_draft_picks.prospect_id','=','prospects.id');
                    $join->where('mock_draft_picks.mock_draft_id','=',$mock_draft_id);
                })
                ->whereRaw('mock_draft_data.'.$pick_string.' > 0 and mock_draft_picks.id is null')
                ->get();

            $team_needs = DB::table('team_needs')
                ->select(DB::raw('team_needs.*'))
                ->where('team_needs.team_id','=',$otc_pick->team_id)
                ->first();

            $total_pick_val = 0;
            foreach($eligible_players as $player){
                $team_need_pos_multiplier = (($team_needs->{strtolower($player->pos)})+2)/3;
                $player->new_player_score = $player->prospect_score * $team_need_pos_multiplier;
                $total_pick_val = $total_pick_val + $player->new_player_score;
            }

            $random_number = rand(0, $total_pick_val);
            $pick_check_int = $random_number;
            $selected_player = null;

            foreach($eligible_players as $player){
                $pick_check_int = $pick_check_int - $player->new_player_score;
                if ($pick_check_int <= 0){
                    $selected_player = $player;
                    break;
                }
            }

            $update_pick = new MockDraftPick;
            $update_pick->mock_draft_id = $mock_draft_id;
            $update_pick->prospect_id = $selected_player->prospect_id;
            $update_pick->team_id = $otc_pick->team_id;
            $update_pick->dynasty_pick_id = $otc_pick->id;
            $update_pick->user_pick = 0;
            $update_pick->save();

            $pick++;
        }

        $answer = array();
        $answer['success'] = true;
        return $answer;
        
    }
}
