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
        }
    }
}
</script>

<template>
    <div>
        <div class="container">
            <div class="row" style="margin:10px; text-align:center">
                <div class="col-sm-12">
                </div>
            </div>
            <div class="row" style="margin:10px; text-align:center">
                <div class="col-sm-4" style="text-align:center">
                    <button class="btn btn-lg btn-light" style="width:80%">
                        <i class="fa-solid fa-3x fa-carrot"></i>
                        <h4>Find Meals by Ingredients</h4>
                    </button>
                </div>
                <div class="col-sm-8" style="margin:auto; text-align:left">
                    <h2>Already have ingredients?</h2>
                    <h5>Filter a large database of meals to find meals to make with the ingredients you have on hand.</h5>
                </div>
            </div>
            <div class="row" style="margin:10px">
                <div class="col-sm-4" style="text-align:center">
                    <button class="btn btn-lg btn-light" style="width:80%; margin-top:20px">
                        <i class="fa-solid fa-3x fa-cart-shopping"></i>
                        <h4>Create a Shopping Cart</h4>
                    </button>
                </div>
                <div class="col-sm-8" style="margin:auto; text-align:left">
                    <h2>Have meals in mind?</h2>
                    <h5>We'll help you create a shopping cart by telling you what (and how much) to buy.</h5>
                </div>
            </div>
            <div class="row" style="margin:10px; text-align:center">
                <div class="col-sm-4" style="text-align:center">
                    <button class="btn btn-lg btn-light" style="width:80%; margin-top:20px" @click="">
                        <i class="fa-solid fa-3x fa-shuffle"></i>
                        <h4>Generate Random Meal</h4>
                    </button>
                </div>
                 <div class="col-sm-8" style="margin:auto; text-align:left">
                    <h2>Just want ideas?</h2>
                    <h5>Our random meal generator has you covered. Optional filter by meal type.</h5>
                </div>
            </div>
        </div>
        <!-- <div class="container">
            <div class="row">
                <div class="col-sm-4" style="text-align:center; margin-top:10px">
                    <h4>Ingredient Search</h4>
                    <input type="text" v-model="ingredient_search" style="color:#000000; width:300px;border-radius: 10px; padding: 5px 10px;"/>
                    <div style="width:290px; margin:auto; display:block" :style="[(ingredient_list.length && show_results) ? { 'border':'solid gray','border-style':'1px' } : '']">
                        <div v-for="(ingredient, index) in ingredient_list" :key="ingredient" class="hover-gray" style="text-align:left; padding:5px" @click="add_ingredient(index)"> 
                            <i class="fa-solid fa-utensils" style="margin-right:5px"></i> {{ ingredient.name }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div style="margin-top:20px">
                        <span v-for="(selected_ingredient, index2) in ingredients_selected" :key="selected_ingredient"><span class="badge badge-success">{{ selected_ingredient.name }} &nbsp; <i class="fa-solid fa-xmark" @click="remove_ingredient(index2)" style="cursor:pointer"></i></span><br/></span>
                    </div>
                </div>
                <div class="col-sm-4" style="margin:auto">
                    <button class="btn btn-success" @click="get_random_meal()">Generate Random Meal</button>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12" style="text-align:center; margin-top:10px">
                    <div v-if="meal" class="container" style="max-width:100%">
                        <div class="row">
                            <div class="col-sm-12" style="text-align:center">
                                <h2 style="text-transform:capitalize">{{ meal.strMeal }}</h2>
                                <img :src="meal.strMealThumb" style="max-height:200px; width:auto"/>
                                <h4>{{ meal.strCategory }}</h4>
                                <h5>{{ meal.strTags }}</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-8">
                                <h3>Instructions</h3>
                                {{ meal.strInstructions }}
                            </div>
                            <div class="col-sm-4">
                                <h3>Ingredients</h3>
                                <ul style="text-align:left">
                                    <li v-for="ingredient in meal.ingredients" :key="ingredient">{{ ingredient.measurement }} {{ ingredient.name }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</template>
