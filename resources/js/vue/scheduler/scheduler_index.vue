<style>
    #scheduler_container{
        color:#ffffff;
    }
    .tab-row{
        background-color:#30302d;
        padding:10px;
        border-radius: 5px;
        margin-top:5px
    }
    .tab-row:hover{
        background-color:#4f4f46;
    }
    .bracket-round{
        max-width:250px; width:250px; display: inline-block; vertical-align:top;
    }
    .matchup{
        margin-bottom:10px;
    }
    .bracket-team{
        max-width:200px; width:200px; height:30px; background-color:#30302D;color:#FFFFFF; display: flex; justify-content: center; align-content: center;flex-direction: column; padding-left: 5px;
    }
    .team-top{
        border-bottom:1px; border-bottom-color: #000000; border-bottom-style:solid; border-top-right-radius: 2px;border-top-left-radius: 2px
    }
    .team-bottom{
        border-bottom-right-radius: 2px;border-bottom-left-radius: 2px
    }
    .winning-team{
        border-left: 10px solid; border-right:1px solid; border-top:1px solid; border-bottom:1px solid; border-color: green;
    }
    .losing-team{
        border-left: 10px solid; border-left-color:gray;
    }
    .add-score{
        cursor:pointer; float:right; margin-right:5px;
    }
    .hover-show{
        display:none;
    }
    .team-badge:hover .hover-show{
        display:inline-block;;
    }
    #teams_table, #teams_table td, #teams_table th{
        padding: 2px
    }
    #teams_table, th{
        text-align: center;
    }
</style>

<script>
import TeamEditor from './team_editor.vue'
import BracketView from './bracket_view.vue'
import LeagueSetup from './league_setup.vue'
export default {
    components: { TeamEditor, BracketView, LeagueSetup },
    mounted() {
        var self = this;
        self.$refs.league_setup.show = 1;
    },
    data() {
        return {
            tournament_id: null,
            show_setup:1
        }
    },
    methods: {
        generate_bracket(){
            var self = this;
            self.tournament_id = self.$refs.team_editor.set_teams();
        },
        create_bracket(event){
            var self = this;
            self.$refs.bracket_view.generate_bracket(event.tournament_id);
        },
        load_bracket(event){
            var self = this;
            self.tournament_id = event.tournament_id
            self.$refs.bracket_view.get_bracket(self.tournament_id);
            self.$refs.team_editor.get_teams(self.tournament_id);
        }
    }
}
</script>

<template>
    <div v-if="show_setup" id="scheduler_container" class="container">
        <div class="row">
            <div style="width:100%; margin-bottom:10px">
                <h1 style="text-align:center">League / Tournament Scheduler</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <league-setup @tournamentLoaded="load_bracket" @startCreateTournament="show_setup = 0" ref="league_setup"></league-setup>
            </div>
        </div>
    </div>
    <div v-else class="container" style="margin-top:20px; height:auto">
        <div class="row">
            <div class="col-sm-12">
                <span class="tab-row" style="display: inline-block;" @click="$refs.bracket_view.show = 1">
                    Bracket
                </span>
                <span class="tab-row" style="display: inline-block;" @click="$refs.team_editor.show = 1">
                    Teams
                </span>
            </div>
            <div class="col-sm-12">
                <team-editor @tournamentCreated="create_bracket" ref="team_editor"></team-editor>
                <bracket-view ref="bracket_view"></bracket-view>
            </div>
        </div>
    </div>
</template>
