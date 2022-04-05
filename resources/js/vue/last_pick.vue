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
            self.get_last_pick(); 
        }
    },
    data() {
        return {
            last_pick: null
        }
    },
    mounted() {
        var self = this;
        self.get_last_pick();
    },
    methods: {
        get_last_pick:function(){
            var self = this;
            var sds = {};
            if (self.mock_draft_id){
                sds.mock_draft_id = self.mock_draft_id
            }
            $.get('get_last_pick', sds, function(response){
                if (response){
                    self.last_pick = response.last_pick;
                } 
            })
        }
    }
}
</script>
<template>
    <div v-if="last_pick" class="container" style="max-width:100%; text-align:center; border:1px white; border-style:solid;">
        <div class="row" style="padding:2px">
            <div class="col-sm-6 p1">
                <img :src="last_pick.prospect_image" style="max-width:50px; height:50px;"/>
            </div>
            <div class="col-sm-6 p1">
                <div style="color:white; font-weight:700; font-size:10px">LAST PICK</div>
                <div style="font-size:12px">{{ last_pick.team_name }}</div>
                <div style="font-size:12px">R{{ last_pick.round }} | P{{ last_pick.pick }}</div>
            </div>
        </div>
        <div class="row"  style="padding:2px">
            <div style="font-size:12px; width:100%; text-align:center; margin-top:5px">{{ last_pick.prospect_name }}</div>
        </div>
    </div>
</template>