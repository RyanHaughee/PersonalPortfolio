<style>
    @media (min-width : 480px) {
        .heading{
            font-size:20px
        }
        .table{
            font-size:14px
        }
        .prospect-image-table{
            max-width:100%; max-width:100px
        }
        .prospect-image{
            max-width:100%; max-width:160px
        }
        .team-logo-table{
            max-width:100%; max-width:45px; max-height:100%;
        }
        .logo-seperator-table{
            border-left: 1px solid white; height:45px; display:inline-block; vertical-align:middle; margin-right:5px; margin-left:5px
        }
        .stat-td{
            text-align:center; width:30px; font-size:14px; border:1px black; border-style:solid; color:#FFFFFF
        }
        .low-padding{
            padding:5px !important
        }
        .mobile-break{
            display:none;
        }
    }
    @media (max-width : 480px) {
        .heading{
            font-size:14px
        }
        .table{
            font-size:10px
        }
        .prospect-image-table{
            max-width:100%; max-width:60px
        }
        .prospect-image{
            max-width:100%; max-width:120px; margin-bottom:5px
        }
        .team-logo-table{
            max-width:100%; max-width:30px; max-height:100%
        }
        .logo-seperator-table{
            border-top: 1px solid white; width:30px; display:inline-block; vertical-align:middle; margin-top:5px; margin-bottom:5px;
        }
        .stat-td{
            text-align:center; width:20px; font-size:10px; border:1px black; border-style:solid; color:#FFFFFF
        }
        .low-padding{
            padding:3px !important
        }
    }

</style>

<script>
export default {
    props:['pos','mock_draft_id','league_id'],
    data() {
        return {
            prospects:[],
            selected_prospect:null,
            selected_index: null,
            show_password: 0,
            password: null,
            password_submit_loading: 0,
            has_error: 1,
            error_message: null
        }
    },
    watch: {
        pos() {
            var self = this;
            self.get_prospects();
        },
        mock_draft_id() {
            var self = this;
            self.get_prospects();
        }
    },
    mounted() {
        var self = this;
        self.get_prospects();
    },
    methods: {
        get_prospects:function(){
            var self = this;
            console.log("getting prospects");
            var sds = {};
            sds.pos = self.pos;
            if (!self.mock_draft_id){
                $.get('/get_prospects', sds, function(response){
                    if (response){
                        self.prospects = response.prospects;
                        self.selected_index = null;
                        self.selected_prospect = null;
                    } 
                })
            } else {
                sds.mock_draft_id = self.mock_draft_id;
                $.get('/get_mock_prospects', sds, function(response){
                    if (response){
                        self.prospects = response.prospects;
                        self.selected_index = null;
                        self.selected_prospect = null;
                    } 
                })
            }

        },
        expand_prospect:function(index){
            var self = this;
            if (self.selected_index == index){
                self.selected_index = null;
                self.selected_prospect = null;
                self.show_password = 0;
            } else {
                self.selected_index = index;
                self.selected_prospect = self.prospects[index];
            }
        },
        show_select:function(){
            var self = this;
            self.show_password = 1;
        },
        check_password:function(prospect_id){
            var self = this;
            self.password_submit_loading = 1
            if (self.mock_draft_id){
                if (self.password == "mock" || self.password == "Mock"){
                    self.select_player(prospect_id);
                } else {
                    self.has_error = 1;
                    self.error_message = "Invalid password.";
                    self.password_submit_loading = 0;
                }
            } else {
                var sds = {};
                sds.password = self.password;
                $.post('/password_check', sds, function(response){
                    if (response){
                        if (response.success){
                            self.select_player(prospect_id);
                        } else {
                            self.has_error = 1;
                            self.error_message = response.message;
                            self.password_submit_loading = 0;
                        }
                    } 
                })
            }
        },
        select_player:function(prospect_id){
            var self = this;
            var sds = {};
            sds.prospect_id = prospect_id;
            sds.mock_draft_id = self.mock_draft_id;
            sds.league_id = self.league_id;
            $.post('/select_prospect', sds, function(response){
                if (response){
                    if (response.success){
                        self.$emit('playerSelected');
                    } else {
                        self.has_error = 1;
                        self.error_message = response.message;
                    }
                } 
            })
            self.password_submit_loading = 0;

        }
    }
}
</script>

<template>
  <div style="max-height: 100vh; overflow-y: scroll;display: inline-block; width:100%; box-sizing: border-box">
      <table class="table" style="border:1px #dee2e6; border-style:solid;">
          <tbody>
              <template style="cursor:pointer" v-for="(prospect, index) in prospects">
                      <tr class="player-row sf" @click="expand_prospect(index)" :key="prospect" :style="[prospect.pick_id ? {'color':'#7C7C7B'} : '']">
                          <template v-if="selected_index == index && selected_prospect">
                              <td colspan="4" style="background-color:#1e2121">
                                  <div style="max-width:100%; margin-bottom:20px">
                                      <div class="row" style="text-align:center;overflow-x: scroll;display: inline-block;max-width:100%">
                                          <div class="col-sm-6">
                                              <img class="prospect-image" v-if="selected_prospect.image" :src="selected_prospect.image"/>
                                              <img class="prospect-image" v-else src="https://www.playerprofiler.com/wp-content/uploads/2014/05/HeadshotSilhouette3.png"/>
                                              <span v-if="selected_prospect.ff_logo"> 
                                                <span style="border-left: 1px solid white; height:120px; display:inline-block; vertical-align:middle; margin-right:5px; margin-left:5px"></span>
                                                <img class="prospect-image" v-if="selected_prospect.ff_logo" :src="selected_prospect.ff_logo"/>
                                             </span>
                                              <div class="heading">{{ selected_prospect.name }}, {{ selected_prospect.pos }}</div>
                                              <div>{{ Math.floor(selected_prospect.height / 12) }}'{{ selected_prospect.height%12 }} | {{ selected_prospect.weight }} lbs</div>
                                              <div>Birthday: {{ selected_prospect.birthday }} ({{ selected_prospect.age }} years old)</div>
                                              <div style="margin-top:20px; text-align:center">
                                                <button v-if="!selected_prospect.pick_id && !show_password" class="btn btn-success" style="width:200px" v-on:click.stop="show_select()">Select</button>
                                                <div v-if="!selected_prospect.pick_id && show_password" class="form-inline" style="display: inline;">
                                                    <div class="form-group mx-auto">
                                                        <input type="text" class="form-control" id="inputPassword2" placeholder="Password" v-model="password" v-on:click.stop>
                                                    </div>
                                                    <button v-if="!password_submit_loading" type="submit" class="btn btn-success ml-2" v-on:click.stop="check_password(selected_prospect.id)">Select</button>
                                                    <div v-if="password_submit_loading" class="spinner-border  text-success  ml-2" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                    <div v-if="has_error && error_message" class="alert alert-danger" role="alert" style="margin:10px">
                                                        {{ error_message }}
                                                        <button type="button" class="close" aria-label="Close" v-on:click.stop="has_error=0">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                </div>
                                              </div>
                                              <div v-if="selected_prospect.highlight_video" style="margin-top:20px; padding:5px">
                                                  <iframe width="90%" height="200" :src="selected_prospect.highlight_video" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                              </div>
                                          </div>
                                          <div class="col-sm-6">
                                              <div style="margin:20px">
                                                  <img v-if="selected_prospect.nfl_team_logo" :src="selected_prospect.nfl_team_logo" style="max-width:100%; max-width:70px"/>
                                                  <img v-else src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png" style="max-width:100%; max-width:70px; max-height:100%"/>
                                                  <span style="border-left: 1px solid white; height:70px; display:inline-block; vertical-align:middle; margin-right:5px; margin-left:5px"></span>
                                                  <img v-if="selected_prospect.cfb_team_logo" :src="selected_prospect.cfb_team_logo" style="max-width:100%; max-width:70px"/>
                                                  <img v-else src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png" style="max-width:100%; max-width:70px; max-height:100%"/>
                                                  </div>
                                              <table style="display:inline-block; vertical-align:middle;">
                                                  <tr>
                                                      <th class="low-padding" colspan="2" style="text-align:center; width:30px; border:1px black; border-style:solid;background-color: #002D62; color:white; font-size:14px">Draft</th>
                                                      <th class="low-padding" colspan="3" style="text-align:center; width:30px; border:1px black; border-style:solid;background-color: #CEBC76; color:white; font-size:14px">Combine</th>
                                                      <th class="low-padding" :colspan="selected_prospect.stat_headers.length" style="text-align:center; width:30px; border:1px black; border-style:solid;background-color: #961B0B; color:white; font-size:14px">Best College Szn</th>
                                                  </tr>
                                                  <tr>
                                                      <th class="low-padding stat-td">RD</th>
                                                      <th class="low-padding stat-td">PK</th>
                                                      <th class="low-padding stat-td">40</th>
                                                      <th class="low-padding stat-td">Vert</th>
                                                      <th class="low-padding stat-td">Broad</th>
                                                      <th v-for="header in selected_prospect.stat_headers" class="low-padding stat-td" :key="header">{{ header }}</th>
                                                  </tr>
                                                  <tr>
                                                      <td class="low-padding stat-td">{{ selected_prospect.draft_round }}</td>
                                                      <td class="low-padding stat-td">{{ selected_prospect.draft_pick }}</td>
                                                      <td class="low-padding stat-td">{{ selected_prospect.fourty_time }}</td>
                                                      <td class="low-padding stat-td">{{ selected_prospect.vertical }}</td>
                                                      <td class="low-padding stat-td">{{ selected_prospect.broad_jump }}</td>
                                                      <td v-for="stat in selected_prospect.stat_stats" class="low-padding stat-td"  :key="stat">{{ stat }}</td>
                                                  </tr>
                                              </table>
                                              <div style="margin-top:20px; font-weight:700">
                                                  THE REPORT
                                              </div>
                                              <div style="margin:10px; line-height:1.5; font-weight:400">
                                                  {{ selected_prospect.scouting_report }}
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </td>
                          </template>
                          <template v-else>
                              <td style="text-align:center;">
                                  <img v-if="prospect.image" class="prospect-image-table" :src="prospect.image" :style="[prospect.pick_id ? {'opacity':'0.5'} : '']"/>
                                  <img v-else class="prospect-image-table" src="https://www.playerprofiler.com/wp-content/uploads/2014/05/HeadshotSilhouette3.png"/>
                              </td>
                              <td>
                                  <div class="heading"><span :style="[prospect.pick_id ? {'text-decoration':'line-through'} : '']">{{ prospect.name }}, {{ prospect.pos }}</span></div>
                                  <div v-if="prospect.pick_id">Round {{ prospect.round }}, Pick {{ prospect.pick }}</div>
                              </td>
                              <td style="text-align:center;">
                                  <table style="text-align:center; font-size:18px">
                                      <tr>
                                          <th style="padding:5px; text-align:center">OVR</th>
                                          <td style="padding:5px; text-align:center">#{{ prospect.ovr_rank }}</td>
                                      </tr>
                                      <tr>
                                          <th style="padding:5px; text-align:center">{{ prospect.pos }}</th>
                                          <td style="padding:5px; text-align:center">#{{ prospect.pos_rank }}</td>
                                      </tr>
                                  </table>
                              </td>
                              <td style="margin:auto;">
                                  <div style="text-align:left;">
                                      <img v-if="prospect.nfl_team_logo" class="team-logo-table" :src="prospect.nfl_team_logo" :style="[prospect.pick_id ? {'opacity':'0.5'} : '']"/>
                                      <img v-else class="team-logo-table" src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png" :style="[prospect.pick_id ? {'opacity':'0.5'} : '']"/>
                                      <br class="mobile-break">
                                      <span class="logo-seperator-table" :style="[prospect.pick_id ? {'opacity':'0.5'} : '']"></span>
                                      <br class="mobile-break">
                                      <img v-if="prospect.cfb_team_logo" class="team-logo-table" :src="prospect.cfb_team_logo" :style="[prospect.pick_id ? {'opacity':'0.5'} : '']"/>
                                      <img v-else class="team-logo-table" src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png" :style="[prospect.pick_id ? {'opacity':'0.5'} : '']"/>
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
</template>