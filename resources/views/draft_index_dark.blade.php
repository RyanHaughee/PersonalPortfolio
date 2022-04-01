<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<script src="https://unpkg.com/vue@3"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="https://kit.fontawesome.com/e45fe32dae.js" crossorigin="anonymous"></script>

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
    .low-padding{
        padding:5px !important
    }
    .stat-td{
        text-align:center; 
        width:30px; 
        font-size:14px; 
        border:1px black; 
        border-style:solid;
        color:#FFFFFF
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

<div class="body" id="app">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-2">
            </div>
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center breaking-news bg-white">
                    <marquee class="news-scroll" behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();"> <span v-for="(pick, index) in draft_picks" style="color:#000000"> <span v-if="index!=0" class="dot"></span> R@{{ pick.round }} | P@{{ pick.pick }} - @{{ pick.prospect_name }}, @{{ pick.school }} @{{ pick.position }} (@{{ pick.team_name }}) </span></marquee>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-sm-2">
                <div style="font-size:18px; font-weight:600; margin-bottom:10px">Menu</div>
                <div class="menu-cat" @click="parent_menu_selected = 'players'"><i v-if="parent_menu_selected == 'players'" class="fa-solid fa-angle-down"></i><i v-else class="fa-solid fa-angle-right"></i> Players</div>
                <span v-if="parent_menu_selected == 'players'">
                    <div class="sub-menu-cat" :style="[filter.pos == 'All' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='All'">All</div>
                    <div class="sub-menu-cat" :style="[filter.pos == 'QB' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='QB'">QB</div>
                    <div class="sub-menu-cat" :style="[filter.pos == 'RB' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='RB'">RB</div>
                    <div class="sub-menu-cat" :style="[filter.pos == 'WR' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='WR'">WR</div>
                    <div class="sub-menu-cat" :style="[filter.pos == 'TE' ? {'background-color':'#1e2121'} : '']" @click="filter.pos='TE'">TE</div>
                </span>
                <div class="menu-cat" @click="parent_menu_selected = 'board'"><i v-if="parent_menu_selected == 'board'" class="fa-solid fa-angle-down"></i><i v-else class="fa-solid fa-angle-right"></i> Board</div>
            </div>
            <div class="col-sm-10" v-if="parent_menu_selected = 'players'">
                <div style="max-height: 100vh; overflow-y: scroll;display: inline-block; width:100%;">
                    <table class="table" style="border:1px #dee2e6; border-style:solid">
                        <tbody>
                            <template v-for="(prospect, index) in prospects" style="cursor:pointer" :key="prospect">
                                <tr class="player-row" @click="expand_prospect(index)">
                                    <template v-if="selected_index == index && selected_prospect">
                                        <td colspan="4" style="background-color:#1e2121;">
                                            <div style="max-width:100%; margin-bottom:20px">
                                                <div class="row" style="text-align:center;overflow-x: scroll;display: inline-block;">
                                                    <div class="col-sm-6">
                                                        <img v-if="selected_prospect.image" :src="selected_prospect.image" style="max-width:100%; max-width:160px"/>
                                                        <img v-else src="https://www.playerprofiler.com/wp-content/uploads/2014/05/HeadshotSilhouette3.png" style="max-width:100%; max-width:160px"/>
                                                        <h4>@{{ selected_prospect.name }}, @{{ selected_prospect.pos }}</h4>
                                                        <h6>@{{ Math.floor(selected_prospect.height / 12) }}'@{{ selected_prospect.height%12 }} | @{{ selected_prospect.weight }} lbs</h6>
                                                        <h6>Birthday: @{{ selected_prospect.birthday }} (@{{ selected_prospect.age }} years old)</h6>
                                                        <div style="margin-top:20px">
                                                            <button type="button" class="btn btn-success" style="width:200px">Select</button>
                                                        </div>
                                                        <div v-if="selected_prospect.highlight_video" style="margin-top:20px; padding:5px">
                                                            <iframe width="100%" height="225" :src="selected_prospect.highlight_video" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div style="margin:20px">
                                                            <img v-if="selected_prospect.cfb_team_logo" :src="selected_prospect.cfb_team_logo" style="max-width:100%; max-width:70px"/>
                                                            <img v-else src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png" style="max-width:100%; max-width:70px; max-height:100%"/>
                                                            <span style="border-left: 1px solid white; height:70px; display:inline-block; vertical-align:middle; margin-right:5px; margin-left:5px"></span>
                                                            <img v-if="selected_prospect.nfl_team_logo" :src="selected_prospect.nfl_team_logo" style="max-width:100%; max-width:70px"/>
                                                            <img v-else src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png" style="max-width:100%; max-width:70px; max-height:100%"/>
                                                            </div>
                                                        <table style="display:inline-block; vertical-align:middle;">
                                                            <tr>
                                                                <th class="low-padding" colspan="2" style="text-align:center; width:30px; font-size:14px; border:1px black; border-style:solid;background-color: #002D62; color:white">Draft</th>
                                                                <th class="low-padding" colspan="3" style="text-align:center; width:30px; font-size:14px; border:1px black; border-style:solid;background-color: #DCCD96; color:white">Combine</th>
                                                                <th class="low-padding" :colspan="selected_prospect.stat_headers.length" style="text-align:center; width:30px; font-size:14px; border:1px black; border-style:solid;background-color: #231F20; color:white">Best College Szn</th>
                                                            </tr>
                                                            <tr>
                                                                <th class="low-padding stat-td">RD</th>
                                                                <th class="low-padding stat-td">PK</th>
                                                                <th class="low-padding stat-td">40</th>
                                                                <th class="low-padding stat-td">Vert</th>
                                                                <th class="low-padding stat-td">Broad</th>
                                                                <th v-for="header in selected_prospect.stat_headers" class="low-padding stat-td">@{{ header }}</th>
                                                            </tr>
                                                            <tr>
                                                                <td class="low-padding stat-td">@{{ selected_prospect.draft_round }}</td>
                                                                <td class="low-padding stat-td">@{{ selected_prospect.draft_pick }}</td>
                                                                <td class="low-padding stat-td">@{{ selected_prospect.fourty_time }}</td>
                                                                <td class="low-padding stat-td">@{{ selected_prospect.vertical }}</td>
                                                                <td class="low-padding stat-td">@{{ selected_prospect.broad_jump }}</td>
                                                                <td v-for="stat in selected_prospect.stat_stats" class="low-padding stat-td">@{{ stat }}</td>
                                                            </tr>
                                                        </table>
                                                        <div style="margin-top:20px; font-size:18px; font-weight:700">
                                                            THE REPORT
                                                        </div>
                                                        <div style="margin:10px; font-size:14px; line-height:1.5; font-weight:400">
                                                            @{{ selected_prospect.scouting_report }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <td style="text-align:center;">
                                            <img v-if="prospect.image" :src="prospect.image" style="max-width:100%; max-width:100px"/>
                                            <img v-else src="https://www.playerprofiler.com/wp-content/uploads/2014/05/HeadshotSilhouette3.png" style="max-width:100%; max-width:100px"/>
                                        </td>
                                        <td>
                                            <h4>@{{ prospect.name }}, @{{ prospect.pos }}</h4>
                                        </td>
                                        <td style="text-align:center;">
                                            <table style="text-align:center; font-size:18px; color:#ffffff">
                                                <tr>
                                                    <th style="padding:5px; text-align:center">OVR</th>
                                                    <td style="padding:5px; text-align:center">#@{{ prospect.ovr_rank }}</td>
                                                </tr>
                                                <tr>
                                                    <th style="padding:5px; text-align:center">@{{ prospect.pos }}</th>
                                                    <td style="padding:5px; text-align:center">#@{{ prospect.pos_rank }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="margin:auto;">
                                            <div style="text-align:left;">
                                                <img v-if="prospect.cfb_team_logo" :src="prospect.cfb_team_logo" style="max-width:100%; max-width:45px"/>
                                                <img v-else src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png" style="max-width:100%; max-width:45px; max-height:100%"/>
                                                <span style="border-left: 1px solid white; height:45px; display:inline-block; vertical-align:middle; margin-right:5px; margin-left:5px"></span>
                                                <img v-if="prospect.nfl_team_logo" :src="prospect.nfl_team_logo" style="max-width:100%; max-width:45px"/>
                                                <img v-else src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png" style="max-width:100%; max-width:45px; max-height:100%"/>
                                            </div>
                                        </td>
                                        <!-- <td>
                                            <button type="button" class="btn btn-xs btn-primary" @click="expand_prospect(index)"><i class="fa-solid fa-angle-right"></i></button>
                                        </td> -->
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
Vue.createApp({
    data() {
        return {
            message: 'Hello Vue!',
            prospects:[],
            selected_prospect:null,
            parent_menu_selected:'players',
            filter:{
                pos: 'All'
            },
            selected_index: null,
            draft_picks: []
        }
    },
    watch: {
        filter: {
            handler(val){
                this.get_prospects();
            },
            deep:true
        }
    },
    mounted() {
        var self = this;
        self.get_prospects();
        self.get_draft_picks();
    },
    methods: {
        get_prospects:function(){
            var self = this;
            var sds = {};
            sds.pos = self.filter.pos;
            $.get('get_prospects', sds, function(response){
                if (response){
                    self.prospects = response.prospects;
                    self.selected_index = null;
                    self.selected_prospect = null;
                } 
            })
        },
        get_draft_picks:function(){
            var self = this;
            $.get('get_draft_picks', function(response){
                if (response){
                    self.draft_picks = response.draft_picks;
                    console.log(self.draft_picks);
                } 
            })
        },
        expand_prospect:function(index){
            var self = this;
            if (self.selected_index == index){
                self.selected_index = null;
                self.selected_prospect = null;
            } else {
                self.selected_index = index;
                self.selected_prospect = self.prospects[index];
            }
        }
    }
}).mount('#app')
</script>