<style>
    body{
        background-color:#000000;
        color:#FFFFFF;
    }
    .table{
        color:#FFFFFF;
    }
    .table>tbody>tr>td{
        vertical-align:middle;
    }
    .menu-cat{
        font-size:16px;
        padding:5px;
        font-weight: 500;
        border-radius: 5px;
    }
    .menu-cat:hover, .player-row:hover, .sub-menu-cat:hover{
        background-color:#1e2121;
        cursor: pointer;
    }
    .sub-menu-cat{
        font-size:14px;
        margin-left:10px;
        padding:5px;
        font-weight: 400;
        border-radius: 5px;
    }

    .news {
        width: 160px
    }

    .news-scroll a {
        text-decoration: none
    }

    .dot { height: 6px; width: 6px; margin-left: 3px; margin-right: 3px; margin-top: 2px !important; background-color: rgb(207, 23, 23); border-radius: 50%; display: inline-block}
</style>


<script>
import Prospects from './prospects.vue'
import DraftTicker from './draft_ticker.vue'
import DraftBoard from './draft_board.vue'
import OtcPick from './otc_pick.vue'
import LastPick from './last_pick.vue'
import MockDraftConfig from './mock_draft_config.vue'
import Countdown from './countdown.vue'
import TradeCenter from './trade_center.vue'
export default {
    components: { Prospects, DraftTicker, DraftBoard, OtcPick, LastPick, MockDraftConfig, Countdown, TradeCenter},
    props: ['league_id'],
    mounted() {
        var self = this;
        self.get_teams();
        self.get_otc_date();
    },
    data() {
        return {
            filter:{
                pos: 'All'
            },
            parent_menu_selected:'players',
            reload_key: 0,
            team_id: null,
            mock_draft_id: null,
            filter_team_id: 'all',
            draft_date: null,
            unique_id:null,
            expand_menu_item: null
        }
    },
    methods: {
        set_parent_menu_selected(){
            this.parent_menu_selected = "board";
        },
        reload_components(){
            var self = this;
            self.reload_key = (self.reload_key+1);
            self.get_otc_date();
        },
        begin_mock(event){
            var self = this;
            self.mock_draft_id = event.mock_draft_id;
            self.unique_id = event.unique_id;
            self.team_id = event.team_id;
        },
        make_next_pick(){
            var self = this;
            var sds = {};
            sds.mock_draft_id = self.mock_draft_id;
            sds.team_id = self.team_id;
            sds.league_id = self.league_id;
            $.get('/draft_function/mock_next_pick', sds, function(response){
                if (response){
                    self.$refs.otc_pick.get_otc_pick();
                    self.$refs.last_pick.get_last_pick();
                    if (self.parent_menu_selected == 'players'){
                        self.$refs.prospects.get_prospects();
                    } else {
                        self.$refs.draft_board.get_all_draft_picks();
                    }
                } 
            })
        },
        sim_to_next_pick(){
            var self = this;
            var sds = {};
            sds.mock_draft_id = self.mock_draft_id;
            sds.team_id = self.team_id;
            sds.league_id = self.league_id;
            $.get('/draft_function/mock_until_next_pick', sds, function(response){
                self.$refs.otc_pick.get_otc_pick();
                self.$refs.last_pick.get_last_pick();
                if (self.parent_menu_selected == 'players'){
                    self.$refs.prospects.get_prospects();
                } else {
                    self.$refs.draft_board.get_all_draft_picks();
                }
            })
        },
        end_mock(){
            var self = this;
            self.mock_draft_id = null;
        },
        get_teams(){
            var self = this;
            var sds = {};
            sds.league_id = self.league_id;
            $.get('/draft_function/get_teams', sds, function(response){
                if (response && response.success){
                    self.teams = response.teams;
                } 
            })
        },
        toggle_parent_menu_selected(item){
            var self = this;
            if (self.parent_menu_selected != item){
                self.parent_menu_selected = item;
            }
            if (self.expand_menu_item != item){
                self.expand_menu_item = item;
            } else {
                self.expand_menu_item = null;
            }
            return;
        },
        get_otc_date(){
            var self = this;
            var sds = {};
            sds.league_id = self.league_id;
            $.get('/draft_function/get_otc_date', sds, function(response){
                var new_otc_date = new Date(response.otc_time);
                self.draft_date = new_otc_date;
            })
            setInterval(() => {
                $.get('/draft_function/get_otc_date', sds, function(response){
                    var new_otc_date = new Date(response.otc_time);
                    if (self.draft_date.getTime() !== new_otc_date.getTime()){
                        console.log("getting here");
                        self.reload_components();
                    } else {
                        console.log("getting here2");
                    }
                })
            }, 5000);
        }
    }
}
</script>

<template class="body">
    <div :key="reload_key">
        <div class="container mt-5">
            <div v-if="mock_draft_id" class="alert alert-warning" role="alert">
                YOU ARE NOW IN A MOCK DRAFT!
            </div>
            <div class="row">
                <div class="col-md-2">
                </div>
                <div class="col-md-10">
                    <draft-ticker :league_id="league_id"></draft-ticker>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-sm-2">
                    <div class="menu-cat" @click="toggle_parent_menu_selected('players')"> <i class="fa-solid fa-circle-user"></i> Players <i v-if="expand_menu_item == 'players'" class="fa-solid fa-angle-down" style="float:right"></i><i v-else class="fa-solid fa-angle-right" style="float:right"></i></div>
                    <span v-if="expand_menu_item == 'players'">
                        <div class="sub-menu-cat" :style="[filter.pos == 'All' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='All'">All</div>
                        <div class="sub-menu-cat" :style="[filter.pos == 'QB' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='QB'">QB</div>
                        <div class="sub-menu-cat" :style="[filter.pos == 'RB' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='RB'">RB</div>
                        <div class="sub-menu-cat" :style="[filter.pos == 'WR' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='WR'">WR</div>
                        <div class="sub-menu-cat" :style="[filter.pos == 'TE' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='TE'">TE</div>
                    </span>
                    <div class="menu-cat" @click="toggle_parent_menu_selected('board')"><i class="fa-solid fa-list-ol"></i> Board <i v-if="expand_menu_item == 'board'" class="fa-solid fa-angle-down" style="float:right"></i><i v-else class="fa-solid fa-angle-right" style="float:right"></i></div>
                    <span v-if="expand_menu_item == 'board'" style="margin-left:10px">
                        Filter:
                        <select class="custom-select custom-select-lg mb-3" placeholder="Filter Team" v-model="filter_team_id">
                            <option value="all" selected>All</option>
                            <option v-for="team in teams" :value="team.id" :key="team">{{ team.team_name }}</option>
                        </select>
                    </span>
                    <div class="menu-cat" @click="toggle_parent_menu_selected('trade')"><i class="fa-solid fa-arrow-right-arrow-left"></i> Trade <i v-if="expand_menu_item == 'trade'" class="fa-solid fa-angle-down" style="float:right"></i><i v-else class="fa-solid fa-angle-right" style="float:right"></i></div>
                    <countdown v-if="draft_date && league_id == 1" :endDate="draft_date" style="margin-top:10px;margin-bottom:10px"></countdown>
                    <otc-pick style="margin-top:5px" :mock_draft_id="mock_draft_id" :league_id="league_id" ref="otc_pick"></otc-pick>
                    <last-pick style="margin-top:5px" :mock_draft_id="mock_draft_id" :league_id="league_id" ref="last_pick"></last-pick>
                    <span v-if="!mock_draft_id">
                        <mock-draft-config @beginmock="begin_mock" :league_id="league_id"></mock-draft-config>
                    </span>
                    <span v-else style="text-align:center">
                        <button type="button" class="btn btn-sm btn-success" style="margin-top:10px" @click="make_next_pick()">Sim Next Pick</button>
                        <button type="button" class="btn btn-sm btn-secondary" style="margin-top:10px" @click="sim_to_next_pick()">Sim To My Pick</button>
                        <button type="button" class="btn btn-sm btn-danger" style="margin-top:10px" @click="end_mock()">End Mock</button>
                        <div v-if="unique_id">
                            <div style="font-weight:700;margin-top:10px;">Draft ID:</div>
                            <input type="text" readonly="readonly" :value="unique_id" style="max-width:100%; color:black"/>
                        </div>
                    </span>
                </div>
                <div class="col-sm-10" v-if="parent_menu_selected == 'players'">
                    <prospects :pos="filter.pos" @playerSelected="reload_components()" :mock_draft_id="mock_draft_id" :league_id="league_id" ref="prospects"></prospects>
                </div>
                <div class="col-sm-10" v-else-if="parent_menu_selected == 'board'">
                    <draft-board :mock_draft_id="mock_draft_id" :pos="filter.pos" :league_id="league_id" :filter_team_id="filter_team_id" ref="draft_board"></draft-board>
                </div>
                <div class="col-sm-10" v-else-if="parent_menu_selected == 'trade'">
                    <trade-center :league_id="league_id" ref="trade_center"></trade-center>
                </div>
            </div>
        </div>
    </div>
</template>
