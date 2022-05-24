<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DynastyTeam;
use App\Models\DynastyTeamValueHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class store_league_info extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynastyleague:storeleagueinfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command that executes one time per day to store the league rankings for that particular day.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $teams = DB::table('dynasty_teams')
            ->select(DB::raw('dynasty_teams.id, dynasty_teams.team_name, dynasty_teams.owner, dynasty_teams.logo'))
            ->where('dynasty_teams.league_id','=',1)
            ->orderBy('owner','asc')
            ->get();

        $team_values = array();
        $qb_values = array();
        $rb_values = array();
        $wr_values = array();
        $te_values = array();
        $dc_values = array();
        $dc23_values = array();
        $dc24_values = array();
        $ovr_values = array();


        $max_obj = new \stdClass;
        $max_obj->total = 0;
        $max_obj->qb = 0;
        $max_obj->rb = 0;
        $max_obj->wr = 0;
        $max_obj->te = 0;
        $max_obj->dc = 0;
        $max_obj->dc23 = 0;
        $max_obj->dc24 = 0;
        $max_obj->ovr = 0;

        $min_obj = new \stdClass;
        $min_obj->total = 1000000;
        $min_obj->qb = 1000000;
        $min_obj->rb = 1000000;
        $min_obj->wr = 1000000;
        $min_obj->te = 1000000;
        $min_obj->dc = 1000000;
        $min_obj->dc23 = 1000000;
        $min_obj->dc24 = 1000000;
        $min_obj->ovr = 1000000;

        foreach($teams as $team){
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

            $players = DynastyTeam::get_team_players($team->id);

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

            if ($value->total > $max_obj->total){
                $max_obj->total = $value->total;
            }
            if ($value->total < $min_obj->total){
                $min_obj->total = $value->total;
            }

            if ($value->qb > $max_obj->qb){
                $max_obj->qb = $value->qb;
            }
            if ($value->qb < $min_obj->qb){
                $min_obj->qb = $value->qb;
            }

            if ($value->rb > $max_obj->rb){
                $max_obj->rb = $value->rb;
            }
            if ($value->rb < $min_obj->rb){
                $min_obj->rb = $value->rb;
            }

            if ($value->wr > $max_obj->wr){
                $max_obj->wr = $value->wr;
            }
            if ($value->wr < $min_obj->wr){
                $min_obj->wr = $value->wr;
            }

            if ($value->te > $max_obj->te){
                $max_obj->te = $value->te;
            }
            if ($value->te < $min_obj->te){
                $min_obj->te = $value->te;
            }


            $team_values[] = $value->total;
            $qb_values[] = $value->qb;
            $rb_values[] = $value->rb;
            $wr_values[] = $value->wr;
            $te_values[] = $value->te;

            $team->value = $value;

            // Draft Capital
            $draft_picks = DB::table('dynasty_future_picks')
                ->select(DB::raw('*'))
                ->where('dynasty_future_picks.current_owner_id','=',$team->id)
                ->orderBy('dynasty_future_picks.current_pick_value','asc')
                ->get();

            $players = DynastyTeam::get_team_players($team->id, false);

            $dc_to_add = 0;

            $dc23_count = 0;
            $dc24_count = 0;


            $dp_index = 0;
            $p_index = 0;
            $pick_to_add = null;

            while ($dp_index < sizeof($draft_picks)){
                


                if ($draft_picks[$dp_index]->current_pick_value > $players[$p_index]->player_value){
                    if (empty($pick_to_add)){
                        $pick_to_add = $draft_picks[$dp_index];

                        if ($draft_picks[$dp_index]->year == 2023){
                            if ($dc23_count < 6){
                                $value->dc23 += $draft_picks[$dp_index]->current_pick_value * (1.5-($dc23_count*0.25));
                            }
                            $dc23_count++;
                        } else {
                            if ($dc24_count < 6){
                                $value->dc24 += $draft_picks[$dp_index]->current_pick_value * (1.5-($dc24_count*0.25));
                            }
                            $dc24_count++;
                        }

                        $dp_index++;
                        $p_index++;
                    } else {
                        $dc_to_add += $pick_to_add->current_pick_value-$players[$p_index]->player_value;
                        $pick_to_add = null;
                    }
                } else {
                    if ($draft_picks[$dp_index]->year == 2023){
                        if ($dc23_count < 6){
                            $value->dc23 += $draft_picks[$dp_index]->current_pick_value * (1.5-($dc23_count*0.25));
                        }
                        $dc23_count++;
                    } else {
                        if ($dc24_count < 6){
                            $value->dc24 += $draft_picks[$dp_index]->current_pick_value * (1.5-($dc24_count*0.25));
                        }
                        $dc24_count++;
                    }

                    $dp_index++;
                } 
            }

            if (!empty($pick_to_add)){
                $dc_to_add += $pick_to_add->current_pick_value-$players[$p_index]->player_value;
                $pick_to_add = null;
            }

            $value->dc = $value->dc23 + $value->dc24;

            if ($value->dc > $max_obj->dc){
                $max_obj->dc = $value->dc;
            }
            if ($value->dc23 > $max_obj->dc23){
                $max_obj->dc23 = $value->dc23;
            }
            if ($value->dc24 > $max_obj->dc24){
                $max_obj->dc24 = $value->dc24;
            }

            if ($value->dc < $min_obj->dc){
                $min_obj->dc = $value->dc;
            }
            if ($value->dc23 < $min_obj->dc23){
                $min_obj->dc23 = $value->dc23;
            }
            if ($value->dc24 < $min_obj->dc24){
                $min_obj->dc24 = $value->dc24;
            }

            $dc_values[] = $value->dc;
            $dc23_values[] = $value->dc23;
            $dc24_values[] = $value->dc24;

            $team->draft_picks = $draft_picks;

            // OVERALL 
            $value->ovr = $value->total + $dc_to_add;

            if ($value->ovr > $max_obj->ovr){
                $max_obj->ovr = $value->ovr;
            }
            if ($value->ovr < $min_obj->ovr){
                $min_obj->ovr = $value->ovr;
            }

            $ovr_values[] = $value->ovr;

        }
        rsort($team_values);
        rsort($qb_values);
        rsort($rb_values);
        rsort($wr_values);
        rsort($te_values);
        rsort($dc_values);
        rsort($dc23_values);
        rsort($dc24_values);
        rsort($ovr_values);
        $total_median = (floatval($team_values[5])+floatval($team_values[6]))/2;
        $qb_median = (floatval($qb_values[5])+floatval($qb_values[6]))/2;
        $rb_median = (floatval($rb_values[5])+floatval($rb_values[6]))/2;
        $wr_median = (floatval($wr_values[5])+floatval($wr_values[6]))/2;
        $te_median = (floatval($te_values[5])+floatval($te_values[6]))/2;
        $dc_median = (floatval($dc_values[5])+floatval($dc_values[6]))/2;
        $dc23_median = (floatval($dc23_values[5])+floatval($dc23_values[6]))/2;
        $dc24_median = (floatval($dc24_values[5])+floatval($dc24_values[6]))/2;
        $ovr_median = (floatval($ovr_values[5])+floatval($ovr_values[6]))/2;

        foreach($teams as $index=>$team){
            // TOTAL
            $team->background = new \stdClass;

            // Rank
            $team->value->total_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->total_rank)){
                if ($team->value->total == $team_values[$array_search]){
                    $team->value->total_rank = $array_search+1;
                    break;
                } else if ($team->value->total > $team_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->total < $team_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            if ($team->value->total >= $total_median){
                $background_opacity = number_format((($team->value->total-$total_median)/($max_obj->total-$total_median)),2);
                $teams[$index]->background->total_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($total_median-$team->value->total)/($total_median-$min_obj->total)),2);
                $teams[$index]->background->total_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->total = number_format(($team->value->total/$max_obj->total)*100,0);

            // QB Rank
            $team->value->qb_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->qb_rank)){
                if ($team->value->qb == $qb_values[$array_search]){
                    $team->value->qb_rank = $array_search+1;
                    break;
                } else if ($team->value->qb > $qb_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->qb < $qb_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            // QB Calc
            if ($team->value->qb >= $qb_median){
                $background_opacity = number_format((($team->value->qb-$qb_median)/($max_obj->qb-$qb_median)),2)/2;
                $teams[$index]->background->qb_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($qb_median-$team->value->qb)/($qb_median-$min_obj->qb)),2)/2;
                $teams[$index]->background->qb_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->qb = number_format(($team->value->qb/$max_obj->qb)*100,0);

            // Rank
            $team->value->rb_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->rb_rank)){
                if ($team->value->rb == $rb_values[$array_search]){
                    $team->value->rb_rank = $array_search+1;
                    break;
                } else if ($team->value->rb > $rb_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->rb < $rb_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            // RB
            if ($team->value->rb >= $rb_median){
                $background_opacity = number_format((($team->value->rb-$rb_median)/($max_obj->rb-$rb_median)),2)/2;
                $teams[$index]->background->rb_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($rb_median-$team->value->rb)/($rb_median-$min_obj->rb)),2)/2;
                $teams[$index]->background->rb_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->rb = number_format(($team->value->rb/$max_obj->rb)*100,0);

            // Rank
            $team->value->wr_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->wr_rank)){
                if ($team->value->wr == $wr_values[$array_search]){
                    $team->value->wr_rank = $array_search+1;
                    break;
                } else if ($team->value->wr > $wr_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->wr < $wr_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            // WR
            if ($team->value->wr >= $wr_median){
                $background_opacity = number_format((($team->value->wr-$wr_median)/($max_obj->wr-$wr_median)),2)/2;
                $teams[$index]->background->wr_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($wr_median-$team->value->wr)/($wr_median-$min_obj->wr)),2)/2;
                $teams[$index]->background->wr_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->wr = number_format(($team->value->wr/$max_obj->wr)*100,0);

            // Rank
            $team->value->te_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->te_rank)){
                if ($team->value->te == $te_values[$array_search]){
                    $team->value->te_rank = $array_search+1;
                    break;
                } else if ($team->value->te > $te_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->te < $te_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            // TE
            if ($team->value->te >= $te_median){
                $background_opacity = number_format((($team->value->te-$te_median)/($max_obj->te-$te_median)),2)/2;
                $teams[$index]->background->te_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($te_median-$team->value->te)/($te_median-$min_obj->te)),2)/2;
                $teams[$index]->background->te_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->te = number_format(($team->value->te/$max_obj->te)*100,0);

            // Rank
            $team->value->dc_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->dc_rank)){
                if ($team->value->dc == $dc_values[$array_search]){
                    $team->value->dc_rank = $array_search+1;
                    break;
                } else if ($team->value->dc > $dc_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->dc < $dc_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            if ($team->value->dc >= $dc_median){
                $background_opacity = number_format((($team->value->dc-$dc_median)/($max_obj->dc-$dc_median)),2);
                $teams[$index]->background->dc_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($dc_median-$team->value->dc)/($dc_median-$min_obj->dc)),2);
                $teams[$index]->background->dc_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->dc = number_format(($team->value->dc/$max_obj->dc)*100,0);

            // Rank
            $team->value->dc23_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->dc23_rank)){
                if ($team->value->dc23 == $dc23_values[$array_search]){
                    $team->value->dc23_rank = $array_search+1;
                    break;
                } else if ($team->value->dc23 > $dc23_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->dc23 < $dc23_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            // DC23
            if ($team->value->dc23 >= $dc23_median){
                $background_opacity = number_format((($team->value->dc23-$dc23_median)/($max_obj->dc23-$dc23_median)),2)/2;
                $teams[$index]->background->dc23_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($dc23_median-$team->value->dc23)/($dc23_median-$min_obj->dc23)),2)/2;
                $teams[$index]->background->dc23_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->dc23 = number_format(($team->value->dc23/$max_obj->dc23)*100,0);

            // Rank
            $team->value->dc24_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->dc24_rank)){
                if ($team->value->dc24 == $dc24_values[$array_search]){
                    $team->value->dc24_rank = $array_search+1;
                    break;
                } else if ($team->value->dc24 > $dc24_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->dc24 < $dc24_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            // DC24
            if ($team->value->dc24 >= $dc24_median){
                $background_opacity = number_format((($team->value->dc24-$dc24_median)/($max_obj->dc24-$dc24_median)),2)/2;
                $teams[$index]->background->dc24_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($dc24_median-$team->value->dc24)/($dc24_median-$min_obj->dc24)),2)/2;
                $teams[$index]->background->dc24_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->dc24 = number_format(($team->value->dc24/$max_obj->dc24)*100,0);

            // Rank
            $team->value->ovr_rank = null;
            $array_search = 6;
            $amount_to_add = 3;
            while (empty($team->value->ovr_rank)){
                if ($team->value->ovr == $ovr_values[$array_search]){
                    $team->value->ovr_rank = $array_search+1;
                    break;
                } else if ($team->value->ovr > $ovr_values[$array_search]){
                    $array_search -= $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                } else if ($team->value->ovr < $ovr_values[$array_search]){
                    $array_search += $amount_to_add;
                    $amount_to_add = round($amount_to_add / 2);
                }
            }

            // OVR
            if ($team->value->ovr >= $ovr_median){
                $background_opacity = number_format((($team->value->ovr-$ovr_median)/($max_obj->ovr-$ovr_median)),2);
                $teams[$index]->background->ovr_rating = "rgb(5,165,81,".$background_opacity.")";
            } else {
                $background_opacity = number_format((($ovr_median-$team->value->ovr)/($ovr_median-$min_obj->ovr)),2);
                $teams[$index]->background->ovr_rating = "rgb(204,36,35,".$background_opacity.")";
            }
            $team->value->ovr = number_format(($team->value->ovr/$max_obj->ovr)*100,0);

            $historical_record = new DynastyTeamValueHistory;
            $historical_record->dynasty_team_id = $team->id;
            $historical_record->value = json_encode($team->value);
            $historical_record->background = json_encode($team->background);
            $historical_record->save();
        }
    }
}
