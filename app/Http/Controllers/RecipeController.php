<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use PDO;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Ingredient;

class RecipeController extends Controller
{
    public function index(){
        return view('recipe_index');
    }

    public function get_random_meal(Request $request){
        $input = $request->all();

        if (empty($input) || empty($input['ingredients'])){
            $response = Http::get('https://www.themealdb.com/api/json/v2/9973533/random.php');
            $response_decoded = json_decode($response);
            $random_meal = $response_decoded->meals[0];
        }

        $i = 1;
        $empty_string = false;
        $ingredient_array = array();
        while ($i <= 20 && $empty_string == false){
            $ingredient_name_string = "strIngredient".$i;
            $ingredient_measurment_string = "strMeasure".$i;

            if (!empty($random_meal->{$ingredient_name_string})){
                $specific_ingredient = new \stdClass();
                $specific_ingredient->name = $random_meal->{$ingredient_name_string};
                
                if (!empty($random_meal->{$ingredient_measurment_string})){
                    $specific_ingredient->measurement = $random_meal->{$ingredient_measurment_string};
                } else {
                    $specific_ingredient->measurement = null;
                }
                $ingredient_array[] = $specific_ingredient;
            } else {
                $empty_string = true;
            }

            $i++;
        }


        $random_meal->ingredients = $ingredient_array;
        
        $answer = array();
        $answer['success'] = true;
        $answer['meal'] = $random_meal;
        return $answer;
    }

    public function search_ingredients(Request $request){
        $input = $request->all();
        $answer = array();
        
        $where = "ingredients.name LIKE '%".$input['query']."%'";

        $query_results = DB::table('ingredients')
            ->select(DB::raw('ingredients.*'))
            ->whereRaw($where)
            ->limit(10)
            ->get();

        $answer['success'] = true;
        $answer['ingredient_list'] = $query_results;
        return $answer;
        
    }
}
