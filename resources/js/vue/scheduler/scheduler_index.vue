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
    #teams_table, td, th{
        padding: 2px
    }
    #teams_table, th{
        text-align: center;
    }
</style>

<script>
import TeamEditor from './team_editor.vue'
import BracketView from './bracket_view.vue'
export default {
    components: { TeamEditor, BracketView },
    mounted() {
        var self = this;
    },
    data() {
        return {
            tournament_select: null,
            tab: null,
            tournament_id: null,
            tournament_code: null,
            schedule_tab: 'bracket'
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
        load_tournament(){
            var self = this;
            var sds = {};
            sds.code = self.tournament_code;
            $.get('load_bracket_by_code', sds, function(response){
                if (response.success){
                    self.tournament_id = response.tournament_id;
                    self.$refs.bracket_view.get_bracket(response.tournament_id);
                    self.tournament_select = "single_elimination";
                    self.$refs.team_editor.get_teams(self.tournament_id);
                }
            })
        },
    }
}
</script>

<template>
    <div id="scheduler_container" class="container">
        <div class="row">
            <div style="width:100%; margin-bottom:10px">
                <h1 style="text-align:center">League / Tournament Scheduler</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6" style="border: 1px; border-right-style: solid; ">
                <div>
                    <h3>Create a Tournament</h3>
                    <h4>What style of tournament?</h4>
                    <select class="custom-select" v-model="tournament_select" style="font-size:14px;height:100%" placeholder="Select">
                        <option value="single_elim">Single Elimination Tournament</option>
                        <option disabled>Option 2</option>
                        <option disabled>Option 3</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div>
                    <h3>Load a Tournament</h3>
                    <h4>Enter your tournament's private code below.</h4>
                    <input type="text" v-model="tournament_code" style="color:black; display:inline-block"/>
                    <button class="btn btn-sm btn-primary" style="margin-left:10px" @click="load_tournament()">Load</button>
                </div>
            </div>
        </div>
    </div>
    <div class="container" style="margin-top:20px">
        <div class="row">
            <div class="col-sm-3">
                <h4>Navigation</h4>
                <div class="tab-row" @click="tab = 'teams'">
                    Teams*
                </div>
                <div class="tab-row" @click="tab = null">
                    Scheduling
                </div>
                <div class="tab-row">
                    Advanced
                </div>
                <div>*Required</div>
                <div>
                    <button class="btn btn-md btn-success" style="margin-top:10px" @click="generate_bracket()">Generate Bracket</button>
                </div>
            </div>
            <div class="col-sm-9">
                <team-editor @tournamentCreated="create_bracket" ref="team_editor"></team-editor>
            </div>
        </div>
    </div>
    <div class="container" style="margin-top:20px; height:auto">
        <div class="row">
            <div class="col-sm-12">
                <span class="tab-row" style="display: inline-block;">
                    Bracket
                </span>
                <span class="tab-row" style="display: inline-block;">
                    Schedule
                </span>
            </div>
        </div>
    </div>
    <bracket-view ref="bracket_view"></bracket-view>
</template>
