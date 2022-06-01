<style>
    #body-style{background-color:#FFFFFF; color:#000000; min-height:100vh; height:100% }
    .team-row{ padding:10px; }
    .team-row:hover, .dynasty-tab:hover, .trade-player-selection:hover{ background-color:#faf5f5; cursor:pointer}
    table{ margin-top:10px }
    .float-l { float:left }
    .float-r { float:right }
    .inline { display:inline-block }
    .rating-table { border:1px solid; text-align:center;}
    .pos-rating-header { width:25% }
    .border-l { border-left:1px solid #000000 }
    .border-r { border-right:1px solid #000000 }
    .border-a { border: 1px solid #000000 }
    .dynasty-tab{ font-size:14px; margin-left:10px; padding:5px; font-weight: 400; border-radius: 5px;}
    .coming-soon{ text-decoration: line-through; color:#8F8F8F}
    @media (min-width : 480px) {
        .dynasty-team-logo{  height:75px; width:75px; }
        .dynasty-team-h1{ font-size:20px; font-weight:600 }
        .dynasty-team-h2{ font-size:16px }
    }
    @media (max-width : 480px) {
        .dynasty-team-logo{ height:50px; width:50px; }
        .dynasty-team-h1{ font-size:16px; font-weight:600 }
        .dynasty-team-h2{ font-size:12px }
    }
</style>

<script>
import DynastyTeamInfo from './dynasty_team_info.vue'
export default {
    components: { DynastyTeamInfo },
    mounted() {
        var self = this;
        self.get_trade_calc_teams();
    },
    data() {
        return {
            tab:'league',
            teams:null,
            trade: {},
            changes: null,
            pick_history: null,
            draft_year: 2022
        }
    },
    watch: {
        'trade.team_1.id': function(){
            var self = this;
            if (self.trade.team_1.id){
                var self = this;
                var sds = {};
                sds.team_id = self.trade.team_1.id;
                $.get('/dynasty_function/get_team_assets', sds, function(response){
                    if (response && response.success){
                        self.trade.team_1.players = response.players;
                        self.trade.team_1.picks = response.picks;
                        self.trade.team_1.players_sent = [];
                        self.trade.team_1.picks_sent = [];
                    }
                })
            }
        },
        'trade.team_2.id': function(){
            var self = this;
            if (self.trade.team_2.id){
                var self = this;
                var sds = {};
                sds.team_id = self.trade.team_2.id;
                $.get('/dynasty_function/get_team_assets', sds, function(response){
                    if (response && response.success){
                        self.trade.team_2.players = response.players;
                        self.trade.team_2.picks = response.picks;
                        self.trade.team_2.players_sent = [];
                        self.trade.team_2.picks_sent = [];
                    }
                })
            }
            
        },
        tab: function(){
            var self = this;
            if (self.tab == 'draft_history'){
                self.draft_year = '2022';
            }
        },
        draft_year: function(){
            var self = this;
            self.get_past_draft();
        }

        
    },
    methods: {
        get_trade_calc_teams(){
            var self = this;
            $.get('/dynasty_function/get_teams', function(response){
                if (response && response.success){
                    self.teams = response.teams
                }
            })
        },
        ordinal_suffix_of(i) {
            var j = i % 10,
                k = i % 100;
            if (j == 1 && k != 11) {
                return i + "st";
            }
            if (j == 2 && k != 12) {
                return i + "nd";
            }
            if (j == 3 && k != 13) {
                return i + "rd";
            }
            return i + "th";
        },
        toggle_player_selection_t1(index){
            var self = this;
            self.trade.team_1.players_sent.push(self.trade.team_1.players[index].id);
            if (self.trade.team_1.players[index].selected){
                self.trade.team_1.players[index].selected = 0;
            } else {
                self.trade.team_1.players[index].selected = 1;
            }
        },
        toggle_pick_selection_t1(index){
            var self = this;
            if (self.trade.team_1.picks[index].selected){
                self.trade.team_1.picks[index].selected = 0;
            } else {
                self.trade.team_1.picks[index].selected = 1;
                self.trade.team_1.picks_sent.push(self.trade.team_1.picks[index].id);
            }
        },
        toggle_player_selection_t2(index){
            var self = this;
            self.trade.team_2.players_sent.push(self.trade.team_2.players[index].id);
            if (self.trade.team_2.players[index].selected){
                self.trade.team_2.players[index].selected = 0;
            } else {
                self.trade.team_2.players[index].selected = 1;
            }
        },
        toggle_pick_selection_t2(index){
            var self = this;
            self.trade.team_2.picks_sent.push(self.trade.team_2.picks[index].id);
            if (self.trade.team_2.picks[index].selected){
                self.trade.team_2.picks[index].selected = 0;
            } else {
                self.trade.team_2.picks[index].selected = 1;
            }
        },
        calculate_trade_value(){
            var self = this;
            var sds = {};
            sds.team_1_id = self.trade.team_1.id;
            sds.team_2_id = self.trade.team_2.id;
            sds.team_1_players_sent = self.trade.team_1.players_sent;
            sds.team_2_players_sent = self.trade.team_2.players_sent;
            sds.team_1_picks_sent = self.trade.team_1.picks_sent;
            sds.team_2_picks_sent = self.trade.team_2.picks_sent;
            $.get('/dynasty_function/compute_rankings_change', sds, function(response){
                self.changes = response.change_array;
            })  

        },
        get_past_draft(){
            var self = this;
            var sds = {};
            sds.year = self.draft_year;
            $.get('/dynasty_function/get_previous_draft', sds, function(response){
                self.pick_history = response.picks;
            }) 
        }
    }
}
</script>

<template>
    <div id="body-style">
        <div>
            <div class="container">
                <div class="row" style="margin-bottom:20px">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-10">
                        <div style="width:100%; text-align:center">
                            <h1>Dynasty League Central</h1>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <h4>Menu</h4>
                        <div class="dynasty-tab" @click="tab = 'league'">Rankings</div>
                        <div class="dynasty-tab coming-soon">League History</div>
                        <div class="dynasty-tab" @click="tab = 'draft_history'">Draft History</div>
                        <div class="dynasty-tab coming-soon">Trade Calculator</div>
                    </div>
                    <div class="col-sm-10">
                       <dynasty-team-info v-if="tab == 'league'"></dynasty-team-info>
                       <div v-if="tab == 'trade'">
                            <div class="container" style="max-width:100%">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h3 style="text-align:center">Team 1</h3>
                                        <select class="form-control" v-model="trade.team_1">
                                            <option selected disabled>Select</option>
                                            <option v-for="team in teams" :key="team" :value="team">{{ team.team_name }}</option>
                                        </select>
                                        <div v-if="trade.team_1" style="text-align:center">
                                            <h4>{{ trade.team_1.team_name }}</h4>
                                            <img class="dynasty-team-logo" :src="trade.team_1.logo"/>
                                            <div style="max-height:200px; overflow-y:scroll; margin-top:30px; border: 1px solid">
                                                <div class="trade-player-selection" v-for="(player, index) in trade.team_1.players" :key="player" style="width:100%" :style="[player.selected ? {'background-color':'#f2e4e4'} : '']" @click="toggle_player_selection_t1(index)">
                                                    {{ player.name }}
                                                </div>
                                                <div class="trade-player-selection" v-for="(pick, index) in trade.team_1.picks" :key="pick" style="width:100%" :style="[pick.selected ? {'background-color':'#f2e4e4'} : '']" @click="toggle_pick_selection_t1(index)">
                                                    {{ pick.original_owner }} {{ pick.year }} {{ ordinal_suffix_of(pick.round) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <h3 style="text-align:center">Team 2</h3>
                                        <select class="form-control" v-model="trade.team_2">
                                            <option selected disabled>Select</option>
                                            <option v-for="team in teams" :key="team" :value="team">{{ team.team_name }}</option>
                                        </select>
                                        <div v-if="trade.team_2" style="text-align:center">
                                            <h4>{{ trade.team_2.team_name }}</h4>
                                            <img class="dynasty-team-logo" :src="trade.team_2.logo"/>
                                            <div style="max-height:200px; overflow-y:scroll; margin-top:30px; border: 1px solid">
                                                <div class="trade-player-selection" v-for="(player, index) in trade.team_2.players" :key="player" style="width:100%" :style="[player.selected ? {'background-color':'#f2e4e4'} : '']" @click="toggle_player_selection_t2(index)">
                                                    {{ player.name }}
                                                </div>
                                                <div class="trade-player-selection" v-for="(pick, index) in trade.team_2.picks" :key="pick" style="width:100%" :style="[pick.selected ? {'background-color':'#f2e4e4'} : '']" @click="toggle_pick_selection_t2(index)">
                                                    {{ pick.original_owner }} {{ pick.year }} {{ ordinal_suffix_of(pick.round) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="width:100%; text-align:center; margin-top:30px; text-align:center">
                                    <div class="col-sm-6" v-for="change in changes" :key="change">
                                        <table style="border:1px solid; text-align:center;width:100%;">
                                            <tr style="height:34px">
                                                <th colspan="8" style="margin:auto; padding:0px">TOTALS</th>
                                            </tr>
                                            <tr>
                                                <th class="pos-rating-header border-a">OVR</th>
                                                <th class="pos-rating-header border-a">ROS</th>
                                                <th class="pos-rating-header border-a">DC</th>
                                            </tr>
                                            <tr>
                                                <td :style="[change.ovr != 0 ? change.ovr > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.ovr }}</td>
                                                <td :style="[change.total != 0 ? change.total > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.total }}</td>
                                                <td :style="[change.dc != 0 ? change.dc > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.dc }}</td>
                                            </tr>
                                        </table>
                                        <table style="border:1px solid; text-align:center;width:100%;">
                                            <tr style="height:34px">
                                                <th colspan="8" style="margin:auto; padding:0px">POS CHANGE</th>
                                            </tr>
                                            <tr>
                                                <th class="pos-rating-header border-a">QB</th>
                                                <th class="pos-rating-header border-a">RB</th>
                                                <th class="pos-rating-header border-a">WR</th>
                                                <th class="pos-rating-header border-a">TE</th>
                                            </tr>
                                            <tr>
                                                <td :style="[change.qb != 0 ? change.qb > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.qb }}</td>
                                                <td :style="[change.rb != 0 ? change.rb > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.rb }}</td>
                                                <td :style="[change.wr != 0 ? change.wr > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.wr }}</td>
                                                <td :style="[change.te != 0 ? change.te > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.te }}</td>
                                            </tr>
                                        </table>
                                        <table style="border:1px solid; text-align:center;width:50%; margin-top:10px">
                                            <tr style="height:34px">
                                                <th colspan="4" style="margin:auto; padding:0px; border:1px solid #000000;">DC CHANGE</th>
                                            </tr>
                                            <tr>
                                                <th class="pos-rating-header border-a">2023</th>
                                                <th class="pos-rating-header border-a">2024</th>
                                            </tr>
                                            <tr>
                                                <td :style="[change.dc23 != 0 ? change.dc23 > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.dc23 }}</td>
                                                <td :style="[change.dc24 != 0 ? change.dc24 > 0 ? {'background-color':'rgb(5,165,81,0.5)'} : {'background-color':'rgb(204,36,35,0.5)'} : '']">{{ change.dc24 }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="row" style="width:100%; text-align:center; margin-top:30px">
                                    <div class="col-sm-12">
                                        <button @click="calculate_trade_value()">CALCULATE</button>
                                    </div>
                                </div>
                            </div>
                       </div>
                       <div v-if="tab == 'draft_history'">
                            <h4 style="display:inline; margin-right:5px">Year:</h4>
                            <select class="form-control" v-model="draft_year" style="width:100px; display:inline">
                                <option selected>2022</option>
                                <option>2021</option>
                                <option disabled>Startup</option>
                            </select>
                           <table style="border:1px solid; text-align:center; width:100%;">
                                <tr>
                                    <th class="border-a">Rd</th>
                                    <th class="border-a">Pk</th>
                                    <th colspan="1" class="border-a">Team</th>
                                    <th colspan="3" class="border-a">Team</th>
                                    <th colspan="3" class="border-a">Name</th>
                                    <th class="border-a">Pos</th>
                                </tr>
                                <tr v-for="pick in pick_history" :key="pick" style="height:30px; border:1px solid; padding:5px">
                                    <td>
                                        {{ pick.round }}
                                    </td>
                                    <td>
                                        {{ pick.pick_num }}
                                    </td>
                                    <td>
                                        <img :src="pick.logo" style="width:50px; height:50px;"/>
                                    </td>
                                    <td colspan="3">
                                        {{ pick.team_name }}
                                    </td>
                                    <td colspan="3">
                                        {{ pick.name }}
                                    </td>
                                    <td>
                                        {{ pick.pos }}
                                    </td>
                                </tr>
                            </table>
                       </div>
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="col-sm-3">
                        <table>
                            <tr>
                                <th colspan="3">
                                    FOUNDATIONAL PLAYERS
                                </th>
                            </tr>
                            <tr>
                                <th>
                                    Name
                                </th>
                                <th>
                                    Pos
                                </th>
                                <th>
                                    Team
                                </th>
                            </tr>
                            <tr v-for="player in team.cornerstone_players" :style="{'background-color': player.background_string }" :key="player">
                                <td style="width: 150px">
                                    {{ player.name }}
                                </td>
                                <td style="width: 50px">
                                    {{ player.pos }}
                                </td>
                                <td style="height:55px;" >
                                    <img :src="player.team_logo" style="width:50px; max-height:50px; height:auto"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</template>
