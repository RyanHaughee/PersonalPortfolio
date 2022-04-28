<style>
    
</style>

<script>
export default {
    components: {},
    mounted() {
        var self = this;
    },
    data() {
        return {
            meal: null
        }
    },
    methods: {
        get_random_meal(){
            var self = this;
            $.get('/recipe/get_random_meal', function(response){
                if (response && response.success){
                    self.meal = response.meal
                    // format_meal();
                }
            });
        },
        format_meal(){
            var self = this;
        },
    }
}
</script>

<template>
    <div style="text-align:center">
        <button class="btn btn-success" @click="get_random_meal()">Generate Random Meal</button>
    </div>
    <div v-if="meal" style="text-align:center">
        <h2 style="text-transform:capitalize">{{ meal.strMeal }}</h2>
        <img :src="meal.strMealThumb" style="max-height:200px; width:auto"/>
        <h4>{{ meal.strCategory }}</h4>
        <h5>{{ meal.strTags }}</h5>
        <div style="margin-top:20px">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>Instructions</h3>
                        {{ meal.strInstructions }}
                    </div>
                    <div class="col-sm-6">
                        <h3>Ingredients</h3>
                        <ul style="text-align:left">
                            <li v-for="ingredient in meal.ingredients" :key="ingredient">{{ ingredient.measurement }} {{ ingredient.name }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
