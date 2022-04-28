<script>
export default {
    props:[],
    data() {
        return {
            new_team: {},
            teams: [],
            show: 0,
        }
    },
    mounted() {
        var self = this;
    },
    methods: {
        add_team(){
            var self = this;
            if (self.new_team.name){
                if (self.new_team.seed){
                    self.new_team.seed = parseInt(self.new_team.seed);
                }
                self.teams.push(self.new_team);
                self.new_team = {};
            }
            self.teams.sort((a,b) => (!a.seed) ? 1 : (!b.seed) ? -1 : (a.seed < b.seed) ? -1 : 1);
            return;
        },
        remove_team(index){
            var self = this;
            self.teams.splice(index, 1);
        },
        edit_team(index){
            var self = this;
            self.new_team = self.teams[index];
            self.teams.splice(index, 1);
        },
        set_teams(){
            var self = this;
            var sds = {};
            sds.teams = self.teams;
            $.post('/tournamnet/set_teams', sds, function(response){
                if (response && response.success){
                    var event = {
                        'tournament_id': response.tournament_id
                    }
                    self.$emit('tournamentCreated',event);
                }
            })
        },
        get_teams(tournament_id){
            var self = this;
            var sds = {};
            sds.tournament_id = tournament_id;
            $.get('/tournamnet/get_tournament_teams', sds, function(response){
                if (response.success){
                    self.teams = response.tournament_teams;
                }
            })
        }
    }
}
</script>
<template>
    <div v-if="show">
        <h4>Add Teams</h4>
        <div>
            <div style="float:left;">
                <label for="seed;display:block;">Seed</label>
                <input id="seed" class="text" style="color:black;height:30px;width:50px;display:block;" v-model="new_team.seed"/>
            </div>
            <div style="float:left;margin-left:10px;">
                <label for="team_name;display:block;">Team Name (required)</label>
                <input id="team_name" class="text" style="color:black;height:30px;width:200px;display:block;" v-model="new_team.name"/>
            </div>
            <div style="float:left;padding-top:20px;margin-left:5px;">
                <label for="submit_btn" style="display:block;"></label>
                <button id="submit_btn" class="btn btn-sm btn-success" style="height:30px;border-radius:0px;margin-bottom:2px;margin-left:5px" @click="add_team()"><i style="cursor:pointer" class="fa-solid fa-plus"></i></button>
            </div>
        </div>
        <div style="display: block; clear: both;"></div>
        <table style="margin-top:10px" id="teams_table">
            <tr>
                <th>Seed</th>
                <th style="width:200px">Team Name</th>
                <th>Actions</th>
            </tr>
            <tr v-for="(team,index) in teams" :key="team">
                <td>
                    <span v-if="team.seed">#{{ team.seed }}</span>
                </td>
                <td>
                    {{ team.name }}
                </td>
                <td>
                    <i @click="edit_team(index)" class="fa-solid fa-pen-to-square" style="cursor:pointer; margin-left:10px"></i>&nbsp;<i @click="remove_team(index)" class="fa-solid fa-xmark" style="cursor:pointer"></i>
                </td>
            </tr>
        </table>
    </div>
</template>