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

    .dot {
        height: 6px;
        width: 6px;
        margin-left: 3px;
        margin-right: 3px;
        margin-top: 2px !important;
        background-color: rgb(207, 23, 23);
        border-radius: 50%;
        display: inline-block
    }
</style>


<script>
import Prospects from './prospects.vue'
import DraftTicker from './draft_ticker.vue'
import DraftBoard from './draft_board.vue'
import OtcPick from './otc_pick.vue'
import LastPick from './last_pick.vue'
export default {
    components: { Prospects, DraftTicker, DraftBoard, OtcPick, LastPick },
    data() {
        return {
            filter:{
                pos: 'All'
            },
            parent_menu_selected:'players',
            reload_key: 0
        }
    },
    methods: {
        log_parent_menu_selected(){
            console.log(this.parent_menu_selected);
        },
        set_parent_menu_selected(){
            this.parent_menu_selected = "board";
        },
        reload_components(){
            var self = this;
            self.reload_key = (self.reload_key+1);
        }
    }
}
</script>

<template>
    <div :key="reload_key">
        <div class="container mt-5">
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
                    <otc-pick style="margin-top:5px"></otc-pick>
                    <last-pick style="margin-top:5px"></last-pick>
                </div>
                <div class="col-sm-10" v-if="parent_menu_selected == 'players'">
                    <prospects :pos="filter.pos" @playerSelected="reload_components()"></prospects>
                </div>
                <div class="col-sm-10" v-else-if="parent_menu_selected == 'board'">
                    <draft-board :pos="filter.pos"></draft-board>
                </div>
            </div>
        </div>
    </div>
</template>
