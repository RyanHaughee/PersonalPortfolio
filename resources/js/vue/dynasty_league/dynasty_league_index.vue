<style>
    #body-style{background-color:#FFFFFF; color:#000000; min-height:100vh; height:100% }
    .team-row{ padding:10px; }
    .team-row:hover{ background-color:#faf5f5; cursor:pointer}
    table{ margin-top:10px }
    .float-l { float:left }
    .rating-table { border:1px solid; text-align:center;}
    .pos-rating-header { width:25% }
    .border-l { border-left:1px solid #000000 }
    .border-r { border-right:1px solid #000000 }
    .border-a { border: 1px solid #000000 }
    @media (min-width : 480px) {
        .dynasty-team-logo{  height:75px; width:75px; }
        .dynasty-team-h1{ font-size:20px }
        .dynasty-team-h2{ font-size:16px }
    }
    @media (max-width : 480px) {
        .dynasty-team-logo{ height:50px; width:50px; }
        .dynasty-team-h1{ font-size:16px }
        .dynasty-team-h2{ font-size:12px }
    }
</style>

<script>
export default {
    components: { },
    mounted() {
        var self = this;
        self.get_teams();
    },
    data() {
        return {
            teams: null,
            selected_index: null
        }
    },
    methods: {
        get_teams(){
            var self = this;
            $.get('/dynasty_function/get_teams', function(response){
                if (response && response.success){
                    self.teams = response.teams
                }
            })
        },
        toggle_index(idx){
            var self = this;
            if (self.selected_index == idx){
                self.selected_index = null;
            } else {
                self.selected_index = idx;
            }
        }

    }
}
</script>

<template>
    <div id="body-style">
        <div>
            <div class="container">
                <div class="row team-row border-a"  v-for="(team, index) in teams" :key="team">
                    <div class="col-sm-12">
                        <div class="container" style="max-width:100%">
                            <div class="row" @click="toggle_index(index)">
                                <div class="col-sm-9" style="margin:auto;">
                                    <img class="float-l dynasty-team-logo" :src="team.logo"/>
                                    <div style="float:left; margin:auto; margin-left:10px">
                                        <span class="dynasty-team-h1">{{ team.team_name }}</span><br>
                                        <span class="dynasty-team-h2">{{ team.owner }}</span><br/>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <table class="rating-table float-l">
                                        <tr>
                                            <td class="border-a" style="height:25px; width:100px">
                                                <span style="font-size:14px; font-weight:600">OVERALL</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:50px; width:100px" :style="{'background-color': team.background.ovr_rating }">
                                                <div style="font-size:20px">{{ team.value.ovr }}</div>
                                                <div style="font-size:12px">#{{ team.value.ovr_rank }} | <span v-if="team.previous">({{ team.value.ovr - team.previous.value.ovr }})</span></div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="rating-table float-l">
                                        <tr>
                                            <td class="border-a" style="height:25px; width:50px">
                                                <span style="font-size:12px">Roster</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:50px; width:50px" :style="{'background-color': team.background.total_rating }">
                                                <div style="font-size:16px">{{ team.value.total }}</div>
                                                <div style="font-size:10px">#{{ team.value.total_rank }} | <span v-if="team.previous">({{ team.value.total - team.previous.value.total }})</span></div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="rating-table float-l">
                                        <tr>
                                            <td class="border-a" style="height:25px; width:50px">
                                                <span style="font-size:12px">DC</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:50px; width:50px" :style="{'background-color': team.background.dc_rating }">
                                                <div style="font-size:16px">{{ team.value.dc }}</div>
                                                <div style="font-size:10px">#{{ team.value.dc_rank }} | <span v-if="team.previous">({{ team.value.dc - team.previous.value.dc }})</span></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <template v-if="index == selected_index">
                                <div class="row" style="margin-top:10px">
                                    <div class="col-sm-3">
                                        <div style="float:left; width:100%;">
                                            <table style="border:1px solid; text-align:center;width:100%;">
                                                <tr style="height:34px">
                                                    <th colspan="8" style="margin:auto; padding:0px">ROSTER STRENGTH</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" class="pos-rating-header border-a">QB</th>
                                                    <th colspan="2" class="pos-rating-header border-a">RB</th>
                                                    <th colspan="2" class="pos-rating-header border-a">WR</th>
                                                    <th colspan="2" class="pos-rating-header border-a">TE</th>
                                                </tr>
                                                <tr>
                                                    <td class="border-l" :style="{'background-color': team.background.qb_rating }">{{ team.value.qb }}</td>
                                                    <td class="border-r" :style="{'background-color': team.background.qb_rating }">#{{ team.value.qb_rank }}</td>
                                                    <td class="border-l" :style="{'background-color': team.background.rb_rating }">{{ team.value.rb }}</td>
                                                    <td class="border-r" :style="{'background-color': team.background.rb_rating }">#{{ team.value.rb_rank }}</td>
                                                    <td class="border-l" :style="{'background-color': team.background.wr_rating }">{{ team.value.wr }}</td>
                                                    <td class="border-r" :style="{'background-color': team.background.wr_rating }">#{{ team.value.wr_rank }}</td>
                                                    <td class="border-l" :style="{'background-color': team.background.te_rating }">{{ team.value.te }}</td>
                                                    <td class="border-r" :style="{'background-color': team.background.te_rating }">#{{ team.value.te_rank }}</td>
                                                </tr>
                                            </table>
                                            <table style="border:1px solid; text-align:center;width:50%; margin-top:10px">
                                                <tr style="height:34px">
                                                    <th colspan="4" style="margin:auto; padding:0px; border:1px solid #000000;">DRAFT CAPITAL</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" class="pos-rating-header border-a">2023</th>
                                                    <th colspan="2" class="pos-rating-header border-a">2024</th>
                                                </tr>
                                                <tr>
                                                    <td class="border-l" :style="{'background-color': team.background.dc23_rating }">{{ team.value.dc23 }}</td>
                                                    <td class="border-r" :style="{'background-color': team.background.dc23_rating }">#{{ team.value.dc23_rank }}</td>
                                                    <td class="border-l" :style="{'background-color': team.background.dc24_rating }">{{ team.value.dc24 }}</td>
                                                    <td class="border-r" :style="{'background-color': team.background.dc24_rating }">#{{ team.value.dc24_rank }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div style="float:left; width:100%">
                                            <table style="border:1px solid; text-align:center; width:100%;">
                                                <tr style="height:34px">
                                                    <th colspan="8" style="margin:auto; padding:0px;">CORNERSTONE PLAYERS</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" class="border-a">Team</th>
                                                    <th class="border-a">Pos</th>
                                                    <th colspan="5" class="border-a">Name</th>
                                                </tr>
                                                <tr v-if="!team.cornerstone_players.length">
                                                    <td colspan="4">None</td>
                                                </tr>
                                                <tr v-for="player in team.cornerstone_players" :key="player" style="height:30px; border-right:1px solid">
                                                    <td colspan="2">
                                                        <img :src="player.team_logo" style="width:30px; max-height:30px; height:auto"/>
                                                    </td>
                                                    <td>
                                                        {{ player.pos }}
                                                    </td>
                                                    <td colspan="5">
                                                        {{ player.name }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div style="float:left; width:100%">
                                            <table style="border:1px solid; text-align:center; width:100%;">
                                                <tr style="height:34px">
                                                    <th colspan="8" style="margin:auto; padding:0px;">FUTURE PICKS</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" class="border-a">Yr</th>
                                                    <th class="border-a">Rd</th>
                                                    <th colspan="5" class="border-a">Originally</th>
                                                </tr>
                                                <tr v-if="!team.draft_picks.length">
                                                    <td colspan="4">None</td>
                                                </tr>
                                                <tr v-for="pick in team.draft_picks" :key="pick" style="height:30px; border-right:1px solid">
                                                    <td colspan="2">
                                                        {{ pick.year }}
                                                    </td>
                                                    <td>
                                                        {{ pick.round }}
                                                    </td>
                                                    <td colspan="5">
                                                        {{ pick.original_team }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div style="float:left">
                                            <table style="border:1px solid; text-align:center;">
                                                <tr>
                                                    <th style="height:25px; width:200px; border: 1px solid #000000" colspan="4">
                                                        TROPHY CASE
                                                    </th>
                                                </tr>
                                                <tr v-if="!team.trophy_row_array.length">
                                                    <td colspan="2">None</td>
                                                </tr>
                                                <tr v-for="row in team.trophy_row_array" :key="row" style="border-right:1px solid">
                                                    <td v-for="trophy in row" :key="trophy" style="width:100px;padding:2px">
                                                        <div style="margin:auto; text-align:center">
                                                            <i :class="trophy.fa_class" :style="trophy.fa_style" style="margin:auto; font-size:1.7em"></i>
                                                        </div>
                                                        <div style="text-align:center">
                                                            {{ trophy.year }}
                                                        </div>
                                                        <div style="text-align:center">
                                                            {{ trophy.award }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </template>
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
