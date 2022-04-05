<style scoped>
    .p1{
        padding:1px;
    }
</style>

<script>
export default {
    props:['mock_draft_id'],
    watch: {
        mock_draft_id(){
            var self = this;
            self.get_otc_pick(); 
        }
    },
    data() {
        return {
            otc_pick: {}
        }
    },
    mounted() {
        var self = this;
        self.get_otc_pick();
    },
    methods: {
        get_otc_pick:function(){
            var self = this;
            var sds = {};
            if (self.mock_draft_id){
                sds.mock_draft_id = self.mock_draft_id
            }
            $.get('get_otc_pick', sds, function(response){
                if (response){
                    self.otc_pick = response.otc_pick;
                } 
            })
        }
    }
}
</script>
<template>
    <div v-if="otc_pick" class="container" style="max-width:100%; text-align:center; border:1px white; border-style:solid;">
        <div class="row" style="padding:2px">
            <div class="col-sm-6 p1">
                <img :src="otc_pick.logo" style="max-width:50px; max-height:50px;"/>
            </div>
            <div class="col-sm-6 p1">
                <div style="color:gold; font-weight:700; font-size:10px">ON THE CLOCK</div>
                <div style="font-size:12px">R{{ otc_pick.round }} | P{{ otc_pick.pick }}</div>
            </div>
        </div>
        <div class="row"  style="padding:2px">
            <div style="font-size:12px; width:100%; text-align:center; margin-top:5px">{{ otc_pick.team_name }}</div>
        </div>
    </div>
</template>