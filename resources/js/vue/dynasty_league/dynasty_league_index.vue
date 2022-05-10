<style>
    #body-style{
        background-color:#FFFFFF;
        color:#000000;
        min-height:100vh;
        height:100%;
    }
    .team-row:hover{
        background-color:#faf5f5;
        cursor:pointer
    }
    table{
        margin-top:10px
    }
    @media (min-width : 480px) {
        .dynasty-team-logo{
            height:75px;
            width:75px;
        }
        .dynasty-team-h1{
            font-size:20px
        }
        .dynasty-team-h2{
            font-size:16px
        }
    }
    @media (max-width : 480px) {
        .dynasty-team-logo{
            height:50px;
            width:50px;
        }
        .dynasty-team-h1{
            font-size:16px
        }
        .dynasty-team-h2{
            font-size:12px
        }
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
                <div class="row team-row"  v-for="(team, index) in teams" :key="team" style="border: 1px solid #000000; padding:10px;">
                    <div class="col-sm-12">
                        <div class="container" style="max-width:100%">
                            <div class="row" @click="toggle_index(index)">
                                <div class="col-sm-9" style="margin:auto; overflow:visible !important">
                                    <img class="dynasty-team-logo" :src="team.logo" style="float:left"/>
                                    <div style="float:left; margin:auto; margin-left:10px">
                                        <span class="dynasty-team-h1">{{ team.team_name }}</span><br>
                                        <span class="dynasty-team-h2">{{ team.owner }}</span><br/>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <table style="border:1px solid; text-align:center; float:left">
                                        <tr>
                                            <td style="height:25px; width:100px">
                                                <span style="font-size:14px; font-weight:600">OVERALL</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:50px; width:100px" :style="{'background-color': team.ovr_rating_background }">
                                                <div style="font-size:20px">{{ team.value.ovr }}</div>
                                                <div style="font-size:12px">(#{{ team.value.ovr_rank }})</div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table style="border:1px solid; text-align:center; float:left">
                                        <tr>
                                            <td style="height:25px; width:50px">
                                                <span style="font-size:12px">Roster</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:50px; width:50px" :style="{'background-color': team.total_rating_background }">
                                                <div style="font-size:16px">{{ team.value.total }}</div>
                                                <div style="font-size:10px">(#{{ team.value.total_rank }})</div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table style="border:1px solid; text-align:center; float:left">
                                        <tr>
                                            <td style="height:25px; width:50px">
                                                <span style="font-size:12px">DC</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:50px; width:50px" :style="{'background-color': team.dc_rating_background }">
                                                <div style="font-size:16px">{{ team.value.dc }}</div>
                                                <div style="font-size:10px">(#{{ team.value.dc_rank }})</div>
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
                                                    <th colspan="2" style="border: 1px solid #000000; width:25%">QB</th>
                                                    <th colspan="2" style="border: 1px solid #000000; width:25%">RB</th>
                                                    <th colspan="2" style="border: 1px solid #000000; width:25%">WR</th>
                                                    <th colspan="2" style="border: 1px solid #000000; width:25%">TE</th>
                                                </tr>
                                                <tr>
                                                    <td style="border-left:1px solid #000000" :style="{'background-color': team.qb_rating_background }">{{ team.value.qb }}</td>
                                                    <td style="border-right:1px solid #000000" :style="{'background-color': team.qb_rating_background }">#{{ team.value.qb_rank }}</td>
                                                    <td style="border-left:1px solid #000000" :style="{'background-color': team.rb_rating_background }">{{ team.value.rb }}</td>
                                                    <td style="border-right:1px solid #000000" :style="{'background-color': team.rb_rating_background }">#{{ team.value.rb_rank }}</td>
                                                    <td style="border-left:1px solid #000000" :style="{'background-color': team.wr_rating_background }">{{ team.value.wr }}</td>
                                                    <td style="border-right:1px solid #000000" :style="{'background-color': team.wr_rating_background }">#{{ team.value.wr_rank }}</td>
                                                    <td style="border-left:1px solid #000000" :style="{'background-color': team.te_rating_background }">{{ team.value.te }}</td>
                                                    <td style="border-right:1px solid #000000" :style="{'background-color': team.te_rating_background }">#{{ team.value.te_rank }}</td>
                                                </tr>
                                            </table>
                                            <table style="border:1px solid; text-align:center;width:50%; margin-top:10px">
                                                <tr style="height:34px">
                                                    <th colspan="4" style="margin:auto; padding:0px; border:1px solid #000000;">DRAFT CAPITAL</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" style="border: 1px solid #000000; width:25%">2023</th>
                                                    <th colspan="2" style="border: 1px solid #000000; width:25%">2024</th>
                                                </tr>
                                                <tr>
                                                    <td style="border-left:1px solid #000000" :style="{'background-color': team.dc23_rating_background }">{{ team.value.dc23 }}</td>
                                                    <td style="border-right:1px solid #000000" :style="{'background-color': team.dc23_rating_background }">#{{ team.value.dc23_rank }}</td>
                                                    <td style="border-left:1px solid #000000" :style="{'background-color': team.dc24_rating_background }">{{ team.value.dc24 }}</td>
                                                    <td style="border-right:1px solid #000000" :style="{'background-color': team.dc24_rating_background }">#{{ team.value.dc24_rank }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div style="float:left; width:100%">
                                            <table style="border:1px solid; text-align:center;width:100%;">
                                                <tr style="height:34px">
                                                    <th colspan="8" style="margin:auto; padding:0px;">CORNERSTONE PLAYERS</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" style="border: 1px solid #000000">Team</th>
                                                    <th style="border: 1px solid #000000">Pos</th>
                                                    <th colspan="5" style="border: 1px solid #000000">Name</th>
                                                </tr>
                                                <tr v-if="!team.cornerstone_players.length">
                                                    <td colspan="4">None</td>
                                                </tr>
                                                <tr v-for="player in team.cornerstone_players" :key="player" style="height:30px">
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
                                                <tr v-for="row in team.trophy_row_array" :key="row" style="border:1px solid">
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
