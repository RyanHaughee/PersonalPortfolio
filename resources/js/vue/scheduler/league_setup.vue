<script>
export default {
    props:[],
    data() {
        return {
            tournament_select: null,
            tournament_code: null,
            show: 0,

        }
    },
    mounted() {
        var self = this;
    },
    methods: {
        load_tournament(){
            var self = this;
            var sds = {};
            sds.code = self.tournament_code;
            $.get('/tournamnet/load_bracket_by_code', sds, function(response){
                if (response.success){
                    self.tournament_id = response.tournament_id;
                    self.tournament_select = "single_elimination";

                    var event = {
                        'tournament_id': response.tournament_id
                    }
                    self.$emit('tournamentLoaded',event);
                }
            })
        },
    }
}
</script>
<template>
    <div v-if="show" class="row">
        <div class="col-sm-6" style="border: 1px; border-right-style: solid; ">
            <div>
                <h4>Create a Tournament</h4>
                <h5>What style of tournament?</h5>
                <select class="custom-select" v-model="tournament_select" style="font-size:14px;height:100%" placeholder="Select">
                    <option value="single_elim">Single Elimination Tournament</option>
                    <option disabled>Option 2</option>
                    <option disabled>Option 3</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div>
                <h4>Load a Tournament</h4>
                <h5>Enter your tournament's private code below.</h5>
                <input type="text" v-model="tournament_code" style="color:black; display:inline-block"/>
                <button class="btn btn-sm btn-primary" style="margin-left:10px" @click="load_tournament()">Load</button>
            </div>
        </div>
    </div>
</template>
    