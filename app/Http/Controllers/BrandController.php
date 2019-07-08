<?php

namespace App\Http\Controllers;

use App\Brand;
use Illuminate\Http\Request;
use Validator;

class BrandController extends Controller
{
    public function storebrand(Request $request)
    {	
    	$msg = ['brand.required' => 'This field is required'];
    	$validator = Validator::make($request->all(), [
    		'brand' => 'required'
    	], $msg);

    	if($validator->fails())
    	{
    		$errors = ['brand' => $validator->errors()->get('brand')];

    		return response()->json($errors, 200);
    	}

        $brand = new Brand();
        $brand->brand = $request->get('brand');
        $brand->save();

        $brands = Brand::all();

        return response()->json(['brands' => $brands, 'success' => 'New brand has been added'],200);

    }
}
