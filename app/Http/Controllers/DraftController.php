<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DynastyPick;

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
            if ($prospect->pos == "WR"){
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

    public function get_otc_pick(){
        // $input = $request->all();

        $otc_pick = DynastyPick::find_otc_pick();

        $response = array();
        $response['otc_pick'] = $otc_pick;
        return $response;
    }

    public function get_last_pick(){
        // $input = $request->all();

        $last_pick = DynastyPick::get_last_pick();

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

        $otc_pick = DynastyPick::find_otc_pick();
        $pick = DynastyPick::find($otc_pick->id);
        $pick->prospect_id = $input['prospect_id'];
        $pick->save();

        $response = array();
        $response['success'] = true;
        return $response;
    }
}
