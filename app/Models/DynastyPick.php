<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynastyPick extends Model
{
    use HasFactory;

    public static function find_otc_pick(){
        $otc_pick = DB::table('dynasty_picks')
            ->select(DB::raw('
                dynasty_picks.*,
                prospects.name as prospect_name,
                prospects.pos as position,
                dynasty_teams.team_name as team_name,
                cfb_teams.school,
                dynasty_teams.logo,
                prospects.image as prospect_image,
                cfb_team_logos.dark_logo as cfb_team_logo,
                nfl_logos.logo as nfl_team_logo,
                dynasty_picks.password'))
            ->leftJoin('prospects','prospects.id','=','dynasty_picks.prospect_id')
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
            ->leftJoin('cfb_teams','cfb_teams.api_id','=','prospects.school_id')
            ->leftJoin('cfb_team_logos','cfb_team_logos.team_api_id','=','prospects.school_id')
            ->leftJoin('nfl_logos','nfl_logos.id','=','prospects.team_id')
            ->whereNull('dynasty_picks.prospect_id')
            ->orderBy('dynasty_picks.round','ASC')
            ->orderBy('dynasty_picks.pick','ASC')
            ->first();

        return $otc_pick;
    }

    public static function get_last_pick(){
        $last_pick = DB::table('dynasty_picks')
            ->select(DB::raw('dynasty_picks.id, prospects.name as prospect_name, prospects.pos as position, dynasty_teams.team_name as team_name, dynasty_picks.round, dynasty_picks.pick, cfb_teams.school, dynasty_teams.logo, prospects.image as prospect_image, cfb_team_logos.dark_logo as cfb_team_logo, nfl_logos.logo as nfl_team_logo,dynasty_picks.password'))
            ->leftJoin('prospects','prospects.id','=','dynasty_picks.prospect_id')
            ->leftJoin('dynasty_teams','dynasty_teams.id','=','dynasty_picks.team_id')
            ->leftJoin('cfb_teams','cfb_teams.api_id','=','prospects.school_id')
            ->leftJoin('cfb_team_logos','cfb_team_logos.team_api_id','=','prospects.school_id')
            ->leftJoin('nfl_logos','nfl_logos.id','=','prospects.team_id')
            ->whereNotNull('dynasty_picks.prospect_id')
            ->orderBy('dynasty_picks.round','DESC')
            ->orderBy('dynasty_picks.pick','DESC')
            ->first();

        return $last_pick;
    }
}
