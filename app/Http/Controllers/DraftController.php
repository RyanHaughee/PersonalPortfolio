<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DynastyPick;
use App\Models\TeamNeed;
use App\Models\MockDraft;
use App\Models\MockDraftPick;
use App\Models\DynastyTrade;

class DraftController extends Controller
{
    public function index(Request $request, $id=null){
        $input = $request->all();
        if (!empty($id)){
            $league_id = $id;
        } else {
            $league_id = 1;
        }
        $otc_pick = DynastyPick::find_otc_pick($league_id);
        return view('draft_index', ['league_id' => $league_id, 'otc_date' => $otc_pick->otc_time]);
    }

    public function league_index(Request $request, $id=null){
        $input = $request->all();
        if (!empty($id)){
            $league_id = $id;
        } else {
            $league_id = 1;
        }
        $otc_pick = DynastyPick::find_otc_pick($league_id);
        return view('draft_index', ['league_id' => $league_id, 'otc_date' => $otc_pick->otc_time]);
    }

    public function get_prospects(Request $request){
        $input = $request->all();

        // We want prospects who have been ranked
        $where_statement = "prospects.pos_rank is not null";

        // Check to see if there is a position filter and if so add to the where statement.
        if (!empty($input) && !empty($input['pos']) && $input['pos'] != 'All'){
            $where_statement = $where_statement . " and prospects.pos = '" . $input['pos'] . "'";
        }

        // Check to see if we are in a mock draft. This changes the way we query the prospects.
        $mock_draft_id = null;
        if (!empty($input['mock_draft_id'])){
            $mock_draft_id = intval($input['mock_draft_id']);
        }

        // We always want to organize by overall rank.
        $order_by = "prospects.ovr_rank";

        if (!empty($mock_draft_id)){
            // If mock draft is not empty, we can assume that we are in a mock draft. 
            // Draft data (pick_id) is gathered from the mock_draft_picks table instead of the dynasty_picks table.
            $prospects = DB::table('prospects')
                ->select(DB::raw('
                    prospects.*, 
                    cfb_team_logos.dark_logo as cfb_team_logo, 
                    nfl_logos.logo as nfl_team_logo, 
                    mock_draft_picks.id as pick_id, 
                    dynasty_teams.logo as ff_logo, 
                    dynasty_picks.round, 
                    dynasty_picks.pick'))
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
        } else {
            // Draft data (pick_id) is gathered from the dynasty_picks table -- there is no mock draft taking place.
            $prospects = DB::table('prospects')
                ->select(DB::raw('
                    prospects.*, 
                    cfb_team_logos.dark_logo as cfb_team_logo, 
                    nfl_logos.logo as nfl_team_logo, 
                    dynasty_picks.id as pick_id, 
                    dynasty_teams.logo as ff_logo, 
                    dynasty_picks.round, 
                    dynasty_picks.pick
                '))
                ->leftJoin('cfb_team_logos','cfb_team_logos.team_api_id','=','prospects.school_id')
                ->leftJoin('nfl_logos','nfl_logos.id','=','prospects.team_id')
                ->leftJoin('dynasty_picks','dynasty_picks.prospect_id','=','prospects.id')
                ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
                ->whereRaw($where_statement)
                ->orderBy($order_by,'asc')
                ->get();
        }

        $today = strtotime(date('Y-m-d'));
        foreach($prospects as $prospect){
            // Finding each prospects age to the first decimal.
            $bday = strtotime($prospect->birthday);
            $age = round(($today-$bday) / (365*60*60*24),1);
            $prospect->age = $age;

            // Setting signficant stats and headers by position
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
        $league_id = $input['league_id'];

        $draft_picks = DB::table('dynasty_picks')
            ->select(DB::raw('
                dynasty_picks.id, 
                prospects.name as prospect_name, 
                prospects.pos as position, 
                dynasty_teams.team_name as team_name, 
                dynasty_picks.round, 
                dynasty_picks.pick, 
                cfb_teams.school'))
            ->leftJoin('prospects','prospects.id','=','dynasty_picks.prospect_id')
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
            ->leftJoin('cfb_teams','cfb_teams.api_id','=','prospects.school_id')
            ->whereNotNull('dynasty_picks.prospect_id')
            ->where('dynasty_picks.league_id','=',$league_id)
            ->orderBy('dynasty_picks.round','ASC')
            ->orderBy('dynasty_picks.pick','ASC')
            ->get();

        $response = array();
        $response['draft_picks'] = $draft_picks;
        return $response;
    }

    public function get_all_draft_picks(Request $request){
        $input = $request->all();

        $mock_draft_id = null;
        if (!empty($input) && !empty($input['mock_draft_id'])){
            $mock_draft_id = $input['mock_draft_id'];
        }

        $league_id = $input['league_id'];
        $where = 'dynasty_picks.league_id = ' . $league_id;

        if (!empty($input['filter_team_id'])){
            if ($input['filter_team_id'] != 'all'){
                $where = $where . ' and dynasty_picks.team_id = ' . $input['filter_team_id'];
            }
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
                ->whereRaw($where)
                ->get();

            $otc_pick = MockDraftPick::find_otc_pick($league_id, $mock_draft_id);

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
                ->whereRaw($where)
                ->get();

            $otc_pick = DynastyPick::find_otc_pick($league_id);
        }

        foreach($all_draft_picks as $pick){
            if (!empty($otc_pick) && ($pick->id == $otc_pick->id)){
                $pick->otc = true;
            } else {
                $pick->otc = false;
            }
        }


        $response = array();
        $response['all_draft_picks'] = $all_draft_picks;
        return $response;
    }

    public function get_otc_pick(Request $request){
        $input = $request->all();

        $league_id = $input['league_id'];
        if (!empty($input['mock_draft_id'])){
            $otc_pick = MockDraftPick::find_otc_pick($league_id, $input['mock_draft_id']);
        } else {
            $otc_pick = DynastyPick::find_otc_pick($league_id);
        }

        $response = array();
        $response['otc_pick'] = $otc_pick;
        return $response;
    }

    public function get_last_pick(Request $request){
        $input = $request->all();

        $league_id = $input['league_id'];
        if (!empty($input['mock_draft_id'])){
            $last_pick = MockDraftPick::get_last_pick($league_id, $input['mock_draft_id']);
        } else {
            $last_pick = DynastyPick::get_last_pick($league_id);
        }

        $response = array();
        $response['last_pick'] = $last_pick;
        return $response;
    }

    public function password_check(Request $request){
        $input = $request->all();
        $league_id = $input['league_id'];

        if (!empty($input) && !empty($input['password'])){
            $password_submitted = $input['password'];
        }
        
        $otc_pick = DynastyPick::find_otc_pick($league_id);

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
        $league_id = $input['league_id'];

        if (empty($input) || (empty($input['prospect_id']))){
            $response = array();
            $response['success'] = false;
            $response['message'] = "No input provided.";
            return $response;
        }

        if (!empty($input['mock_draft_id'])){
            $otc_pick = MockDraftPick::find_otc_pick($league_id, $input['mock_draft_id']);
            $pick = new MockDraftPick;
            $pick->mock_draft_id = $input['mock_draft_id'];
            $pick->prospect_id = $input['prospect_id'];
            $pick->team_id = $otc_pick->team_id;
            $pick->dynasty_pick_id = $otc_pick->id;
            $pick->league_id = $league_id;
            $pick->user_pick = 1;
            $pick->save();
        } else {
            $otc_pick = DynastyPick::find_otc_pick($league_id);
            $pick = DynastyPick::find($otc_pick->id);
            $pick->prospect_id = $input['prospect_id'];
            $pick->save();

            // Set date
            date_default_timezone_set('US/Eastern');
            if (date("H", strtotime('+6 hours')) >= 0 && date("H", strtotime('+6 hours')) < 8){
                $fourteen_hrs = date("Y-m-d H:i:s", strtotime('+14 hours')); 
                $new_otc_pick = DynastyPick::find_otc_pick($league_id);
                $edited_pick = DynastyPick::find($new_otc_pick->id);
                $edited_pick->otc_time = $fourteen_hrs; 
                $edited_pick->save();

            } else {
                $six_hrs = date("Y-m-d H:i:s", strtotime('+6 hours')); 
                $new_otc_pick = DynastyPick::find_otc_pick($league_id);
                $edited_pick = DynastyPick::find($new_otc_pick->id);
                $edited_pick->otc_time = $six_hrs; 
                $edited_pick->save();
            }
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

    public function get_teams(Request $request){
        $input = $request->all();
        $league_id = $input['league_id'];
        $answer = array();
        $teams = DB::table('dynasty_teams')
            ->select(DB::raw('dynasty_teams.*, team_needs.qb, team_needs.rb, team_needs.wr, team_needs.te'))
            ->leftJoin('team_needs','team_needs.team_id','=','dynasty_teams.id')
            ->where('dynasty_teams.league_id','=',$league_id)
            ->get();

        if (empty($teams)){
            $answer['success'] = false;
            $answer['message'] = "No teams found.";
        }

        $answer['success'] = true;
        $answer['teams'] = $teams;
        return $answer;
    }

    public function start_mock(Request $request){
        $input = $request->all();
        $league_id = $input['league_id'];
        $team_id = $input['selected_team'];
        $answer = array();

        // Generate random string for identifying mock draft in the future
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 10; $i++) {
            $randstring = $randstring.$characters[rand(0, (strlen($characters)-1))];
        }


        $mock_draft = new MockDraft;
        $mock_draft->league_id = $league_id;
        $mock_draft->selected_team_id = $team_id;
        $mock_draft->unique_id = $randstring;
        $mock_draft->save();

        $answer['success'] = true;
        $answer['mock_draft_id'] = $mock_draft->id;
        $answer['unique_id'] = $mock_draft->unique_id;
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
        $league_id = $input['league_id'];

        $otc_pick = MockDraftPick::find_otc_pick($league_id, $mock_draft_id);

        if ($otc_pick->team_id == $team_id){
            $answer = array();
            $answer['success'] = false;
            $answer['message'] = "You are the team on the clock!";
            return $answer;
        }

        $pick_string = "pick_".$otc_pick->overall;

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

        $past_picks = DB::table('dynasty_picks')
            ->select(DB::raw('prospects.pos, prospects.id as prospect_id'))
            ->leftJoin('mock_draft_picks','mock_draft_picks.dynasty_pick_id','=','dynasty_picks.id')
            ->leftJoin('prospects','prospects.id','=','mock_draft_picks.prospect_id')
            ->where('mock_draft_picks.mock_draft_id','=',$mock_draft_id)
            ->where('mock_draft_picks.team_id','=',$otc_pick->team_id)
            ->get();

        foreach($past_picks as $past_pick){
            if ($past_pick->pos == 'QB' || $past_pick->pos == 'TE'){
                $team_needs->{strtolower($past_pick->pos)} = $team_needs->{strtolower($past_pick->pos)} - 2;
            } else {
                $team_needs->{strtolower($past_pick->pos)} = $team_needs->{strtolower($past_pick->pos)} - 1;
            }
            if ($team_needs->{strtolower($past_pick->pos)} < 1){
                $team_needs->{strtolower($past_pick->pos)} = 1;
            }
        }

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
        $update_pick->league_id = $league_id;
        $update_pick->save();

        $answer = array();
        $answer['success'] = true;
        return $answer;
        
    }

    public function mock_until_next_pick(Request $request){
        $input = $request->all();
        $league_id = $input['league_id'];

        if (empty($input['team_id']) || empty($input['mock_draft_id'])){
            $answer = array();
            $answer['success'] = false;
            $answer['message'] = "No team_id or mock_draft_id provided to the controller.";
        } else {
            $team_id = $input['team_id'];
            $mock_draft_id = $input['mock_draft_id'];
        }

        $otc_pick = MockDraftPick::find_otc_pick($league_id,$mock_draft_id);
        $pick = $otc_pick->overall;

        if ($league_id == 1){
            $max_pick = 38;
        } else if ($league_id == 2){
            $max_pick = 30;
        }

        while($pick <= $max_pick){
            $otc_pick = MockDraftPick::find_otc_pick($league_id, $mock_draft_id);

            $pick_string = "pick_".$otc_pick->overall;

            if ($team_id == $otc_pick->team_id){
                break;
            }

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

            $past_picks = DB::table('dynasty_picks')
                ->select(DB::raw('prospects.pos, prospects.id as prospect_id'))
                ->leftJoin('mock_draft_picks','mock_draft_picks.dynasty_pick_id','=','dynasty_picks.id')
                ->leftJoin('prospects','prospects.id','=','mock_draft_picks.prospect_id')
                ->where('mock_draft_picks.mock_draft_id','=',$mock_draft_id)
                ->where('mock_draft_picks.team_id','=',$otc_pick->team_id)
                ->get();
    
            foreach($past_picks as $past_pick){
                if ($past_pick->pos == 'QB' || $past_pick->pos == 'TE'){
                    $team_needs->{strtolower($past_pick->pos)} = $team_needs->{strtolower($past_pick->pos)} - 2;
                } else {
                    $team_needs->{strtolower($past_pick->pos)} = $team_needs->{strtolower($past_pick->pos)} - 1;
                }
                if ($team_needs->{strtolower($past_pick->pos)} < 1){
                    $team_needs->{strtolower($past_pick->pos)} = 1;
                }
            }

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
            $update_pick->league_id = $league_id;
            $update_pick->save();

            $pick++;
        }

        $answer = array();
        $answer['success'] = true;
        return $answer;
        
    }

    public function get_mock(Request $request){
        $input = $request->all();
        $league_id = $input['league_id'];
        $unique_id = $input['unique_id'];
        $answer = array();

        $mock_draft = DB::table('mock_drafts')
            ->select(DB::raw('mock_drafts.id'))
            ->where('mock_drafts.unique_id','=',$unique_id)
            ->where('mock_drafts.league_id','=',$league_id)
            ->first();

        if (empty($mock_draft) || empty($mock_draft->id)){
            $answer['success'] = false;
            $answer['message'] = "No mock draft found for the provided string.";
            return $answer;
        }

        $mock_draft_check = MockDraft::find($mock_draft->id);

        if (empty($mock_draft_check)){
            $answer['success'] = false;
            $answer['message'] = "No mock draft found for the provided string.";
            return $answer;
        } else {
            $answer['success'] = true;
            $answer['mock_draft_id'] = $mock_draft_check->id;
            $answer['unique_id'] = $unique_id;
            $answer['selected_team_id'] = $mock_draft_check->selected_team_id;
            return $answer;
        }



    }

    public function get_tradeable_draft_picks(Request $request){
        $input = $request->all();
        $league_id = $input['league_id'];

        $where = "dynasty_picks.prospect_id IS NULL and dynasty_picks.league_id = ". $league_id;

        if (!empty($input['team_id'])){
            $team_id = $input['team_id'];
            $where = " dynasty_picks.team_id = ".$team_id;
        }

        $picks = DB::table('dynasty_picks')
            ->select(DB::raw('dynasty_picks.id, dynasty_picks.overall, dynasty_picks.round, dynasty_picks.pick, dynasty_teams.team_name, dynasty_teams.id as team_id'))
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
            ->whereRaw($where)
            ->get();

        $answer = array();
        $answer['success'] = true;
        $answer['picks'] = $picks;
        return $answer;

    }

    public function initiate_trade(Request $request){
        $input = $request->all();
        $answer = array();

        // Initialize pick array to be a string. This is how it is saved in the database, so we want to format it the same.
        $team_1_picks = null;
        if (!empty($input['team_1_picks_sent'])){
            foreach($input['team_1_picks_sent'] as $pick){
                $team_1_picks = $team_1_picks.$pick['id']." ";
            } 
        }

        // Initialize pick array to be a string. This is how it is saved in the database, so we want to format it the same.
        $team_2_picks = null;
        if (!empty($input['team_2_picks_sent'])){
            foreach($input['team_2_picks_sent'] as $pick){
                $team_2_picks = $team_2_picks.$pick['id']." ";
            }
        }

        $team_1_id = $input['team_1_id'];
        $team_2_id = $input['team_2_id'];

        // We are reversing the teams because we want to see if a trade like this has already been created.
        $pending_trade_check = DB::table('dynasty_trades')
            ->select(DB::raw('dynasty_trades.*'))
            ->where('team_1_id','=',$team_2_id)
            ->where('team_2_id','=',$team_1_id)
            ->where('team_1_receives','=',$team_2_picks)
            ->where('team_2_receives','=',$team_1_picks)
            ->where('verified','!=', 1)
            ->first();

        if (!empty($pending_trade_check)){
            $pending_trade = DynastyTrade::find($pending_trade_check->id);

            // team_1_picks now belong to team 2
            if (!empty($team_1_picks)){
                $t1_picks_array = explode(" ",$team_1_picks);
                foreach($t1_picks_array as $pick_id){
                    if (!empty($pick_id)){
                        $pick = DynastyPick::find($pick_id);
                        $team = DynastyTeam::find($team_2_id);
                        $pick->team_id = $team_2_id;
                        $pick->password = $team->password;
                        $pick->save();
                    }
                }
            }

            // team_2_picks now belong to team 1
            if (!empty($team_2_picks)){
                $t2_picks_array = explode(" ",$team_2_picks);
                foreach($t2_picks_array as $pick_id){
                    if (!empty($pick_id)){
                        $pick = DynastyPick::find($pick_id);
                        $team = DynastyTeam::find($team_1_id);
                        $pick->team_id = $team_1_id;
                        $pick->password = $team->password;
                        $pick->save();
                    }
                }
            }

            $pending_trade->verified = 1;
            $pending_trade->save();

            $rtn_message = "Trade has been processed.";

        } else {
            $trade = new DynastyTrade;
            $trade->league_id = $input['league_id'];
            $trade->team_1_id = $team_1_id;
            $trade->team_2_id = $team_2_id;
            $trade->team_1_receives = $team_1_picks;
            $trade->team_2_receives = $team_2_picks;
            $trade->verified = 0;
            $trade->save();

            $rtn_message = "Trade created. Waiting on accept.";
        }

        $answer['success'] = true;
        $answer['message'] = $rtn_message;
        return $answer;
    }

    public function team_password_check(Request $request){
        $input = $request->all();
        $answer = array();

        $team = DB::table('dynasty_teams')
            ->select(DB::raw("*"))
            ->where('dynasty_teams.id','=',$input['team_id'])
            ->first();

        if ($team->password == $input['password']){
            $answer['success'] = true;
            $answer['verified'] = 1;
        } else {
            $answer['success'] = true;
            $answer['verified'] = 0;
        }
        return $answer;

    }

    public function get_pending_trades(Request $request){
        $input = $request->all();
        $answer = array();
        $league_id = $input['league_id'];

        $pending_trades = DB::table('dynasty_trades')
            ->select(DB::raw("team_sent.team_name as team_sent_team_name, team_to_accept.team_name as team_to_accept_team_name, team_sent.logo as team_sent_logo, team_to_accept.logo as team_to_accept_logo, dynasty_trades.*"))
            ->join('dynasty_teams as team_sent','team_sent.id','=','dynasty_trades.team_1_id')
            ->join('dynasty_teams as team_to_accept','team_to_accept.id','=','dynasty_trades.team_2_id')
            ->where('dynasty_trades.league_id','=',$league_id)
            ->where('dynasty_trades.verified','!=',1)
            ->get();

        $answer['success'] = true;
        $answer['pending_trades'] = $pending_trades;
        return $answer;
    }

    public function get_otc_date(Request $request){
        $input = $request->all();
        $answer = array();
        $league_id = $input['league_id'];

        $otc_pick = DynastyPick::find_otc_pick($league_id);
        $otc_time = $otc_pick->otc_time; 

        $answer['success'] = true;
        $answer['otc_time'] = $otc_time;
        $answer['pick_id'] = $otc_pick->id;
        return $answer;
    }
}
