<style scoped>
    .p1{
        padding:1px;
    }
    .switch { position: relative;display: inline-block;width: 35px;height: 20px; margin-top:5px}

    .switch input { opacity: 0; width: 0; height: 0;}

    .slider {position: absolute;cursor: pointer;top: 0;left: 0;right: 0;bottom: 0;background-color: #ccc;-webkit-transition: .4s;transition: .4s;}

    .slider:before { position: absolute; content: ""; height: 15px; width: 15px; left: 2px; bottom: 2px; background-color: white; -webkit-transition: .4s;transition: .4s; }

    input:checked + .slider { background-color: #2196F3;}

    input:focus + .slider { box-shadow: 0 0 1px #2196F3; }

    input:checked + .slider:before {-webkit-transform: translateX(15px); -ms-transform: translateX(15px); transform: translateX(15px); }

    /* Rounded sliders */
    .slider.round { border-radius: 20px; }

    .slider.round:before { border-radius: 50%;}
</style>

<script>
export default {
    props:['league_id'],
    data() {
        return {
            mock_mode: 0,
            mock_draft_config: 1,
            selected_team: null,
            teams: [],
            pick: null,
            mock_draft_id: null,
            unique_id: null,
            load_draft_id: null,
            error_message:  null
        }
    },
    mounted() {
        var self = this;
        self.get_teams();
    },
    methods: {
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
        begin_draft(){
            var self = this;
            self.mock_draft_config = 0;
            var sds = {};
            sds.league_id = self.league_id;
            sds.selected_team = self.selected_team.id;
            $.post('/draft_function/start_mock', sds, function(response){
                if (response && response.success){
                    self.mock_draft_id = response.mock_draft_id;
                    self.unique_id = response.unique_id
                } 
                var event = {
                    'mock_draft_id': self.mock_draft_id,
                    'team_id': self.selected_team.id,
                    'unique_id': self.unique_id
                }
                self.$emit('beginmock',event);
            })
        },
        load_draft(){
            var self = this;
            self.error_message = null;
            var sds = {};
            sds.league_id = self.league_id;
            sds.unique_id = self.load_draft_id;
            $.get('/draft_function/load_mock', sds, function(response){
                if (response && response.success){
                    self.mock_draft_id = response.mock_draft_id;
                    self.unique_id = response.unique_id;
                     var event = {
                        'mock_draft_id': self.mock_draft_id,
                        'team_id': response.selected_team_id,
                        'unique_id': self.unique_id
                    }
                    self.$emit('beginmock',event);
                } else {
                    self.error_message = "Invalid code.";
                }
            })

        }
    }
}
</script>
<template>
    <div>
        <div style="font-weight:700;margin-top:10px;text-align:center">Mock Draft Mode:</div>
        <div style="text-align:center">
            <label class="switch">
                <input type="checkbox" v-model="mock_mode">
                <span class="slider round"></span>
            </label>
        </div>
        <div v-if="mock_mode && mock_draft_config" style="text-align:center">
            <div style="font-weight:700;margin-top:10px;">Load Draft:</div>
            <input type="text" style="max-width:100%; color:black" v-model="load_draft_id"/>
            <span v-if="error_message" class="badge badge-danger">{{ error_message }}</span>
            <button v-if="load_draft_id && load_draft_id.length > 8" type="button" class="btn btn-sm btn-success" style="margin-top:10px; margin-bottom:10px" @click="load_draft()">Load Draft</button>
            <div style="font-weight:700;margin-top:20px;margin-bottom:20px;">--- OR ---</div>
            <div style="font-weight:700;margin-top:10px;">Select Team:</div>
            <select class="custom-select custom-select-lg mb-3" placeholder="Select Team" v-model="selected_team">
                <option v-for="team in teams" :value="team" :key="team">{{ team.team_name }}</option>
            </select>
            <span v-if="selected_team && selected_team.logo"> 
                <img style="height:70px; width:70px" v-if="selected_team.logo" :src="selected_team.logo"/><br/>
                <button type="button" class="btn btn-sm btn-success" style="margin-top:10px; margin-bottom:10px" @click="begin_draft()">Begin Draft</button>
            </span>
        </div>
    </div>
</template>