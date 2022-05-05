<script>
export default {
    props:['league_id'],
    watch: {
        sending_team_id(){
            var self = this;
            if (self.sending_team_id){
                var sds = {};
                sds.league_id = self.league_id;
                sds.team_id = self.sending_team_id;
                $.get('/draft_function/get_tradeable_draft_picks', sds, function(response){
                    if (response){
                        self.sending_picks = response.picks;
                    }
                })
            }
        },
        receiving_team_id(){
            var self = this;
            if (self.receiving_team_id){
                var sds = {};
                sds.league_id = self.league_id;
                sds.team_id = self.receiving_team_id;
                $.get('/draft_function/get_tradeable_draft_picks', sds, function(response){
                    if (response){
                        self.receiving_picks = response.picks;
                    }
                })
            }
        },
    },
    data() {
        return {
            sending_picks: [],
            receiving_picks: [],
            pick_index_to_send: null,
            picks_to_send: [],
            pick_index_to_receive: null,
            picks_to_receive: [],
            sending_team_id: null,
            receiving_team_id: null,
            password:null,
            message: null,
            teams:[]
        }
    },
    mounted() {
        var self = this;
        self.get_league_teams();
    },
    methods: {
        get_tradeable_draft_picks(){
            var self = this;
            var sds = {};
            sds.league_id = self.league_id
            $.get('/draft_function/get_tradeable_draft_picks', sds, function(response){
                if (response){
                    self.sending_picks = response.picks;
                    self.receiving_picks = response.picks;
                }
            })
        },
        get_league_teams(){
            var self = this;
            var sds = {};
            sds.league_id = self.league_id;
            $.get('/draft_function/get_teams', sds, function(response){
                if (response && response.success){
                    self.teams = response.teams;
                } 
            })
        },
        send_pick(){
            var self = this;
            self.picks_to_send.push(self.sending_picks[self.pick_index_to_send]);
            self.pick_index_to_send = null;
        },
        receive_pick(index){
            var self = this;
            self.picks_to_receive.push(self.receiving_picks[self.pick_index_to_receive]);
            self.pick_index_to_receive = null;
        },
        initiate_trade(){
            var self = this;
            var sds = {};
            self.message = null;
            if (self.sending_team_id){
                sds.team_1_id = self.sending_team_id;
            } else {
                self.message = "Please select your team.";
            }
            if (self.receiving_team_id){
                sds.team_2_id = self.receiving_team_id;
            } else {
                self.message = "Please select their team.";
            }

            if (!self.picks_to_send.length && !self.picks_to_receive.length){
                self.message = "Please select at least one pick to trade.";
            } else {
                sds.team_1_picks_sent = self.picks_to_send;
                sds.team_2_picks_sent = self.picks_to_receive;
            }
            sds.league_id = self.league_id;
            $.post('/draft_function/initiate_trade', sds, function(response){
                if (response){
                    self.message = response.message;
                }
            })
        },
        check_team_password(){
            var self = this;
            var sds = {};
            self.message = null;
            sds.team_id = self.sending_team_id;
            sds.password = self.password;
            $.get('/draft_function/team_password_check', sds, function(response){
                if (response){
                    if (response.verified){
                        self.initiate_trade();
                    } else {
                        self.message="Invalid password.";
                    }
                } else {
                    self.message="Error while checking password.";
                }
            })
        }

        
    }
}
</script>
<template>
    <div style="max-height: 100vh; overflow-y: scroll;display: inline-block; width:100%; box-sizing: border-box">
        <div class="col-sm-12">
            <div style="text-align:center">
                <h1>Trade Center</h1>
                <h5>*** WILL NOT WORK IN A MOCK DRAFT ***</h5>
                <h5>Since there is no login system, here is where picks will be traded.</h5>
                <h5>Select the picks you are sending and receiving. You will then be asked to enter your password. Once both teams have submitted identical trades, the site will update and reflect the new pick owners.</h5>
            </div>
        </div>
        <div class="col-sm-6">
            <div style="text-align:center">
                <h3 style="margin-bottom: 10px">Select your team.</h3>
                <select class="form-control" style="margin-bottom:10px" v-model="sending_team_id" >
                    <option v-for="team in teams" :key="team" :value="team.id">{{ team.team_name }} - {{team.owner}}</option>
                </select>
                <div v-if="sending_picks.length" style="margin-bottom: 10px">Add picks to send</div>
                <select v-if="sending_picks.length" class="form-control" style="margin-bottom:10px" v-model="pick_index_to_send" >
                    <option v-for="(pick, index) in sending_picks" :key="pick" :value="index">#{{ pick.overall }} ({{ pick.round }}.{{ pick.pick }}) - {{ pick.team_name }}</option>
                </select>
                <button v-if="pick_index_to_send != null" class="btn btn-info" @click="send_pick()" style="margin-bottom:20px">Add</button>
                <h3 v-if="picks_to_send.length">Sending:</h3>
                <div v-for="sent_pick in picks_to_send" :key="sent_pick">
                    #{{ sent_pick.overall }} ({{ sent_pick.round }}.{{ sent_pick.pick }}) - {{ sent_pick.team_name }}
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div style="text-align:center">
                <h3 style="margin-bottom: 10px">Select their team.</h3>
                <select class="form-control" style="margin-bottom:10px" v-model="receiving_team_id" >
                    <option v-for="team in teams" :key="team" :value="team.id">{{ team.team_name }} - {{team.owner}}</option>
                </select>
                <div v-if="receiving_picks.length" style="margin-bottom: 10px">Add picks to receive</div>
                <select v-if="receiving_picks.length" class="form-control" style="margin-bottom:10px" v-model="pick_index_to_receive" >
                    <option v-for="(pick, index) in receiving_picks" :key="pick" :value="index">#{{ pick.overall }} ({{ pick.round }}.{{ pick.pick }}) - {{ pick.team_name }}</option>
                </select>
                <button v-if="pick_index_to_receive != null" class="btn btn-info" @click="receive_pick()" style="margin-bottom:20px">Add</button>
                <h3 v-if="picks_to_receive.length">Receiving:</h3>
                <div v-for="received_pick in picks_to_receive" :key="received_pick">
                    #{{ received_pick.overall }} ({{ received_pick.round }}.{{ received_pick.pick }}) - {{ received_pick.team_name }}
                </div>
            </div>
        </div>
        <div v-if="picks_to_send.length || picks_to_receive.length" class="col-sm-12" style="text-align:center; margin-top:40px">
            <div class="form-group mx-auto" style="width:200px">
                <input type="text" class="form-control" id="inputPassword2" placeholder="Password" v-model="password" v-on:click.stop>
            </div>
            <button class="btn btn-success" @click="check_team_password()" style="margin-bottom:20px" :disabled="!password">Submit Trade</button>
            <div class="alert alert-danger" v-show="message" v-cloak> <i class="fa fa-close" style="float:right; cursor:pointer" @click="message=null"></i> {{message}}</div>
        </div>
    </div>
</template>