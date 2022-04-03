<style>
 @media (min-width : 480px) {
        .heading{
            font-size:24px
        }
        .table-board{
            font-size:18px
        }
        .vm{
            text-align:center;
            border: 1px white;
            border-style:solid;
        }
        .team-logo-board{
            width:60px; height:60px;
        }
        .team-logo-table-board{
            max-width:100%; max-width:45px; max-height:100%;
        }
        .logo-seperator-table-board{
            border-left: 1px solid white; height:45px; display:inline-block; vertical-align:middle; margin-right:5px; margin-left:5px
        }
        .mobile-break{
            display:none;
        }
        .prospect-image-board{
            max-width:60px; max-height:60px;
        }
        .table>tr>td{
            vertical-align:middle;
        }
 }
 @media (max-width : 480px) {
        .heading{
            font-size:14px
        }
        .table{
            font-size:10px;
        }
        .vm{
            text-align:center;
            border: 1px white;
            border-style:solid;
            margin: auto;
        }
        .team-logo-board{
            width:50px; height:50px;
        }
        .team-logo-table-board{
            max-width:100%; max-width:25px; max-height:100%
        }
        .logo-seperator-table-board{
            border-top: 1px solid white; width:30px; display:inline-block; vertical-align:middle; margin-top:2px; margin-bottom:2px;
        }
        .prospect-image-board{
            max-width:50px; max-height:50px;
        }
        .table>tr>td{
            vertical-align:middle;
        }
 }

</style>

<script>
export default {
    data() {
        return {
            draft_picks: []
        }
    },
    mounted() {
        var self = this;
        self.get_all_draft_picks();
    },
    methods: {
        get_all_draft_picks:function(){
            var self = this;
            $.get('get_all_draft_picks', function(response){
                if (response){
                    self.draft_picks = response.all_draft_picks;
                    console.log(self.draft_picks);
                } 
            })
        }
    }
}
</script>
<template>
    <div style="overflow-y: scroll; max-height:100vh">
        <table class="table-board" style="width:100%;">
            <tr class="vm" v-for="pick in draft_picks" :key="pick" style="margin:5px" :style="[pick.otc ? {'border':'2px gold','border-style':'solid'} : '']">
                <td>
                    {{ pick.round }}.{{ pick.pick }}
                </td>
                <td>
                    <div><img class="team-logo-board" :src="pick.logo"/></div>
                </td> 
                <td>
                    {{ pick.team_name }} 
                </td>
                <td v-if="pick.prospect_name">
                    <span><img class="prospect-image-board" :src="pick.prospect_image"/></span>
                </td>
                <td v-else>
                    <span style="margin-right:10px"><img src="https://www.playerprofiler.com/wp-content/uploads/2014/05/HeadshotSilhouette3.png" style="max-width:60px; max-height:60px;"/></span> 
                </td>
                <td>
                    <span v-if="pick.prospect_name" style="margin-left:10px">{{ pick.prospect_name }}, {{ pick.position }}</span>
                    <span v-else-if="pick.otc" style="color:#FFD701; font-weight:700;">ON THE CLOCK</span>
                    <span v-else>-</span>
                </td>
                <td v-if="pick.prospect_name">
                    <img v-if="pick.nfl_team_logo" class="team-logo-table-board" :src="pick.nfl_team_logo"/>
                    <img v-else class="team-logo-table-board" src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png"/>
                    <br class="mobile-break">
                    <span class="logo-seperator-table-board"></span>
                    <br class="mobile-break">
                    <img v-if="pick.cfb_team_logo" class="team-logo-table-board" :src="pick.cfb_team_logo"/>
                    <img v-else class="team-logo-table-board" src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Question_mark_white_icon.svg/1200px-Question_mark_white_icon.svg.png"/>
                </td>
                <td v-else>
                </td>
            </tr>
        </table>
    </div>
</template>