<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;
use App\Models\DynastyFuturePick;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test(){
        $picks = DB::table('dynasty_picks')
            ->select(DB::raw('*'))
            ->where('league_id','=',1)
            ->orderBy('round','asc')
            ->orderBy('pick','asc')
            ->get();

        $future_picks = DB::table('dynasty_future_picks')
            ->select(DB::raw('*'))
            ->where('year','=',2022)
            ->orderBy('round','asc')
            ->orderBy('pick_num','asc')
            ->get();

        foreach($picks as $index=>$pick){
            $update_pick = DynastyFuturePick::find($future_picks[$index]->id);
            $update_pick->current_owner_id = $pick->team_id;
            $update_pick->save();
        }
    }
}
