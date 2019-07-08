<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Validator;
class CategoryController extends Controller
{
    public function storecategory(Request $request)
    {   
    	$msg = ['category.required' => 'This field is required'];

    	$validator = Validator::make($request->all(), [
    		'category' => 'required'
    	], $msg);

    	if($validator->fails())
    	{
    		$errors = ['category' => $validator->errors()->get('category')];

    		return response()->json($errors,200);
    	}

        $category = new Category();
        $category->category = $request->get('category');
        $category->save();
        
        $categories = Category::all();

        return response()->json(['categories' => $categories,'success' => 'New category has been added'],200);
    }
}
