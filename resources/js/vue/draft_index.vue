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
export default {
    components: { Prospects, DraftTicker, DraftBoard, OtcPick, LastPick, MockDraftConfig },
    mounted() {
        var self = this;
    },
    data() {
        return {
            filter:{
                pos: 'All'
            },
            parent_menu_selected:'players',
            reload_key: 0,
            team_id: null,
            mock_draft_id: 15
        }
    },
    methods: {
        set_parent_menu_selected(){
            this.parent_menu_selected = "board";
        },
        reload_components(){
            var self = this;
            self.reload_key = (self.reload_key+1);
        },
        begin_mock(event){
            var self = this;
            self.mock_draft_id = event.mock_draft_id;
            self.team_id = event.team_id;
        },
        make_next_pick(){
            var self = this;
            var sds = {};
            sds.mock_draft_id = self.mock_draft_id;
            sds.team_id = self.team_id;
            $.get('mock_next_pick', sds, function(response){
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
            $.get('mock_until_next_pick', sds, function(response){
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
        end_mock(){
            var self = this;
            self.mock_draft_id = null;
        }
    }
}
</script>

<template>
    <div :key="reload_key">
        <div class="container mt-5">
            <div v-if="mock_draft_id" class="alert alert-warning" role="alert">
                YOU ARE NOW IN A MOCK DRAFT!
            </div>
            <div class="row">
                <div class="col-md-2">
                </div>
                <div class="col-md-10">
                    <draft-ticker></draft-ticker>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-sm-2">
                    <div style="font-size:18px; font-weight:600; margin-bottom:10px">Menu</div>
                    <div class="menu-cat" @click="parent_menu_selected='players'"><i v-if="parent_menu_selected == 'players'" class="fa-solid fa-angle-down"></i><i v-else class="fa-solid fa-angle-right"></i> Players</div>
                    <span v-if="parent_menu_selected == 'players'">
                        <div class="sub-menu-cat" :style="[filter.pos == 'All' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='All'">All</div>
                        <div class="sub-menu-cat" :style="[filter.pos == 'QB' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='QB'">QB</div>
                        <div class="sub-menu-cat" :style="[filter.pos == 'RB' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='RB'">RB</div>
                        <div class="sub-menu-cat" :style="[filter.pos == 'WR' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='WR'">WR</div>
                        <div class="sub-menu-cat" :style="[filter.pos == 'TE' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='TE'">TE</div>
                    </span>
                    <div class="menu-cat" v-on:click="parent_menu_selected='board'"><i v-if="parent_menu_selected == 'board'" class="fa-solid fa-angle-down"></i><i v-else class="fa-solid fa-angle-right"></i> Board</div>
                    <otc-pick style="margin-top:5px" :mock_draft_id="mock_draft_id" ref="otc_pick"></otc-pick>
                    <last-pick style="margin-top:5px" :mock_draft_id="mock_draft_id" ref="last_pick"></last-pick>
                    <span v-if="!mock_draft_id">
                        <mock-draft-config @beginmock="begin_mock"></mock-draft-config>
                    </span>
                    <span v-else>
                        <button type="button" class="btn btn-sm btn-success" style="margin-top:10px" @click="make_next_pick()">Sim Next Pick</button>
                        <button type="button" class="btn btn-sm btn-secondary" style="margin-top:10px" @click="sim_to_next_pick()">Sim To My Pick</button>
                        <button type="button" class="btn btn-sm btn-danger" style="margin-top:10px" @click="end_mock()">End Mock</button>
                    </span>
                </div>
                <div class="col-sm-10" v-if="parent_menu_selected == 'players'">
                    <prospects :pos="filter.pos" @playerSelected="reload_components()" :mock_draft_id="mock_draft_id" ref="prospects"></prospects>
                </div>
                <div class="col-sm-10" v-else-if="parent_menu_selected == 'board'">
                    <draft-board :mock_draft_id="mock_draft_id" :pos="filter.pos" ref="draft_board"></draft-board>
                </div>
            </div>
        </div>
    </div>
</template>
