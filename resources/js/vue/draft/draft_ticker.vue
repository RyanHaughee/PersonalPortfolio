<script>
export default {
    props:['league_id'],
    data() {
        return {
            draft_picks: []
        }
    },
    mounted() {
        var self = this;
        self.get_draft_picks();
    },
    methods: {
        get_draft_picks:function(){
            var self = this;
            var sds = {};
            sds.league_id = self.league_id;
            $.get('/get_draft_picks', sds, function(response){
                if (response){
                    self.draft_picks = response.draft_picks;
                } 
            })
        }
    }
}
</script>
<template>
    <div class="d-flex justify-content-between align-items-center breaking-news bg-white">
        <marquee class="news-scroll" behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();"> <span v-for="(pick, index) in draft_picks" style="color:#000000" :key="pick"> <span v-if="index!=0" class="dot"></span> R{{ pick.round }} | P{{ pick.pick }} - {{ pick.prospect_name }}, {{ pick.school }} {{ pick.position }} ({{ pick.team_name }}) </span></marquee>
    </div>
</template>