<script>
export default {
    props:[],
    data() {
        return {
            tournament: [],
            show_edit:null,
            edit_score_game_id:null,
            tournament_id: null,
            show: 0
        }
    },
    mounted() {
        var self = this;
    },
    methods: {
        generate_bracket(tournament_id){
            var self = this;
            var sds = {};
            self.tournament_id = tournament_id
            sds.tournament_id = tournament_id;
            $.get('/tournamnet/generate_bracket', sds, function(response){
                if (response.success){
                    self.get_bracket(tournament_id);
                }
            })
        },
        get_bracket(tournament_id=null){
            var self = this;
            var sds = {};
            if (tournament_id){
                self.tournament_id = tournament_id;
            }
            sds.tournament_id = self.tournament_id;
            $.get('/tournamnet/get_bracket', sds, function(response){
                if (response.success){
                    self.tournament = response.tournament;
                }
            })
        },
        edit_score(game_id){
            var self = this;
            self.edit_score_game_id = game_id;
        },
        submit_score(index, index2){
            var self = this;
            var game = self.tournament[index].games[index2];
            var sds = {};
            sds.game_id = game.id;
            sds.tournament_id = self.tournament_id;
            sds.team_1_score = game.team_1_score;
            sds.team_2_score = game.team_2_score;
            $.post('/tournamnet/submit_score', sds, function(response){
                if (response.success){
                    self.get_bracket();
                }
            })
            self.edit_score_game_id = null;
        }
    }
}
</script>
<template>

    <div v-if="show" class="container" style="white-space: nowrap;overflow-x:scroll">
        <div class="bracket-round" v-for="(round, index) in tournament" :key="round">
            <span v-for="(game, index2) in round.games" :key="game">
                <div class="matchup" :style="[index2 == 0 ? {marginTop: round.top_margin+'px'} : {marginTop: round.matchup_margin+'px'}]" @mouseover="show_edit=game.tournament_game_id">
                    <div style="height:60px; width:15px; display:inline-block; vertical-align:top;align-items : center;">
                        <div style="position:relative;top:50%;transform: translateY(-50%);font-size:10px">
                            {{ game.tournament_game_id }}
                        </div>
                    </div>
                    <div v-if="(edit_score_game_id == game.tournament_game_id) && game.tournament_game_id" style="display:inline-block">
                        <div class="bracket-team team-top" style="height:60px;display:inline-block !important; text-align:center">
                            <table>
                                <tr>
                                    <td style="width:50%;padding-top:3px">
                                        {{ game.team_1 }}
                                    </td>
                                    <td style="width:30%;padding-top:3px">
                                        <input v-model="game.team_1_score" class="number" style="width:30px; background-color:#000000; border:1px #808080; border-style:solid;width:90%; text-align:center;"/>
                                    </td>
                                    <td style="width:20%;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:50%;padding-top:3px">
                                        {{ game.team_2 }}
                                    </td>
                                    <td style="width:30%;padding-top:3px">
                                        <input v-model="game.team_2_score" class="number" style="width:30px; background-color:#000000; border:1px #808080; border-style:solid;width:90%; text-align:center;"/>
                                    </td>
                                    <td style="width:20%;">
                                        <button class="btn btn-xs btn-success" style="height:24px; width:24px" @click="submit_score(index, index2)"><i class="fa-solid fa-check" style="height:14px;width:10px"></i></button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div v-else style="display:inline-block">
                        <div v-if="game.tournament_game_id" class="bracket-team team-top" :class="[(game.team_1_score > game.team_2_score) ? 'winning-team' : 'losing-team']">
                            <span v-if="game.team_1">
                                {{ game.team_1 }} <span v-if="game.team_1_score"> - {{ game.team_1_score}}</span>
                                <i class="fa-solid fa-pen-to-square add-score" @click="edit_score(game.tournament_game_id)" :style="[(show_edit && show_edit == game.tournament_game_id && game.team_1 && game.team_2 && (edit_score_game_id != game.tournament_game_id)) ? { display:''} : { display:'none' }]"></i>
                            </span>
                            <span v-else style="color:#808080"><i>Winner of Game #{{ game.team_1_origin}}</i></span>
                        </div>
                        <div v-if="game.tournament_game_id" class="bracket-team team-bottom" :class="[(game.team_2_score > game.team_1_score) ? 'winning-team' : 'losing-team']">
                            <span v-if="game.team_2">
                                {{ game.team_2 }} <span v-if="game.team_2_score"> - {{ game.team_2_score}}</span>
                            </span>
                            <span v-else style="color:#808080"><i>Winner of Game ##]{{ game.team_2_origin}}</i></span>
                        </div>
                    </div>
                </div>
            </span>
        </div>
    </div>
</template>