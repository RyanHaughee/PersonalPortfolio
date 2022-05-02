<style>
    .hover-gray:hover{
        background-color:#696969;
        cursor:pointer
    }
    
</style>

<script>
export default {
    components: {},
    mounted() {
        var self = this;
    },
    data() {
        return {
            meal: null,
            ingredient_search: null,
            ingredient_list: [],
            ingredients_selected: [],
            awaitingSearch: false,
            show_results:0
        }
    },
    watch: {
        ingredient_search: function (){
            var self = this;
            if (!self.awaitingSearch) {
                setTimeout(() => {
                    self.search_ingredients();
                    self.awaitingSearch = false;
                }, 1000); // 1 sec delay
            }
            self.awaitingSearch = true;
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
        search_ingredients(){
            var self = this;
            var sds = {};
            self.show_results = 1;
            sds.query = self.ingredient_search
            $.get('/recipe/search_ingredients',sds,function(response){
                if (response.success){
                    self.ingredient_list = response.ingredient_list;
                }
            });
        },
        add_ingredient(index){
            var self = this;
            self.ingredients_selected.push(self.ingredient_list[index]);
        },
        remove_ingredient(index){
            var self = this;
            self.ingredients_selected.splice(index);
        },
        clear_ingredient_array(){
            var self = this;
            console.log("test2");
        }
    }
}
</script>

<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-sm-6" style="text-align:center; margin-top:10px" v-click-outside="clear_ingredient_array()">
                    <h4>Choose Ingredients</h4>
                    <input type="text" v-model="ingredient_search" style="color:#000000; width:300px;border-radius: 10px; padding: 5px 10px;"/>
                    <div style="width:290px; margin:auto" :style="[(ingredient_list.length && show_results) ? { 'border':'solid gray','border-style':'1px' } : '']">
                        <div v-for="(ingredient, index) in ingredient_list" :key="ingredient" class="hover-gray" style="text-align:left; padding:5px" @click="add_ingredient(index)"> 
                            <i class="fa-solid fa-utensils" style="margin-right:5px"></i> {{ ingredient.name }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-6" style="text-align:center; margin-top:10px">
                    <div>
                        <h4>Selected Ingredients</h4>
                        <span v-for="(selected_ingredient, index2) in ingredients_selected" :key="selected_ingredient"><span class="badge badge-success">{{ selected_ingredient.name }} &nbsp; <i class="fa-solid fa-xmark" @click="remove_ingredient(index2)" style="cursor:pointer"></i></span><br/></span>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:20px">
                <div class="col-sm-12" style="text-align:center">
                    <button class="btn btn-success" @click="get_random_meal()">Generate Random Meal</button>
                </div>
            </div>
        </div>
        <div v-if="meal" class="container">
            <div class="row">
                <div class="col-sm-12" style="text-align:center">
                    <h2 style="text-transform:capitalize">{{ meal.strMeal }}</h2>
                    <img :src="meal.strMealThumb" style="max-height:200px; width:auto"/>
                    <h4>{{ meal.strCategory }}</h4>
                    <h5>{{ meal.strTags }}</h5>
                </div>
            </div>
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
</template>
