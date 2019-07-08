<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Category;
use App\Brand;
use App\Tax;
use Validator;
use DB;
class ProductController extends Controller
{

    public function index()
    {   
        $products = DB::select('SELECT
                                a.id,
                                a.itemcode,
                                a.itemname,
                                b.category,
                                c.brand,
                                a.avgprice,
                                a.srp,
                                a.onhand
                                FROM products a 
                                LEFT JOIN categories b on a.category = b.id 
                                LEFT JOIN brands c on a.brand = c.id');

        return view('pages.product.index',compact('products'));
    }

    public function createproduct()
    {	

        $categories = Category::all();
        $brands = Brand::all();
        $taxes = Tax::all();
    	return view('pages.product.create',compact('categories', 'brands', 'taxes'));
    }

    public function storeproduct(Request $request)
    {	 
        
        $msg = ['itemcode.required' => 'This field is required',
                'itemname.required' => 'This field is required',
                'srp.required' => 'This field is required',
                'srp.numeric' => 'Please enter a valid number',
                'srp.between' => 'Please enter a valid number',
                'dp.required' => 'This field is required',
                'dp.numeric' => 'Please enter a valid number',
                'dp.between' => 'Please enter a valid number',];
                
        $validator = Validator::make($request->all(),[
            'itemcode' => 'required',
            'itemname' => 'required',
            'srp' => 'required|numeric|between:0, 999999999999.999999999999',
            'dp' => 'required|numeric|between:0, 999999999999.999999999999'
        ], $msg);

        if ($validator->fails()) 
        {   
            $errors =['itemcode' => $validator->errors()->get('itemcode'),
                      'itemname' => $validator->errors()->get('itemname'),
                      'dp' => $validator->errors()->get('dp'),
                      'srp' => $validator->errors()->get('srp'),
                      'category' => $validator->errors()->get('category'),
                      'brand' => $validator->errors()->get('brand')];

            return response()->json($errors,200);
        }


        $products = new Product();
        $products->itemcode = $request->get('itemcode');
        $products->itemname = $request->get('itemname');
        $products->avgprice = 0;
        $products->srp = $request->get('srp');
        $products->category = $request->get('category');
        $products->brand = $request->get('brand');
        $products->tax = $request->get('tax');
        $products->onhand = 0;
        $products->status = $request->get('status');
        $products->save();

        return response()->json(['success' => 'Record has been added.'],200);
        // return response()->json($request->all(),200);
    }


    public function editproduct($productid)
    {   

        $products = Product::find($productid);
        $categories = Category::all();
        $brands = Brand::all();
        $taxes = Tax::all();
        return view('pages.product.edit', compact('products', 'categories', 'brands', 'productid', 'taxes'));
    }

    public function updateproduct(Request $request)
    {
        $msg = ['itemcode.required' => 'This field is required',
                'itemname.required' => 'This field is required',
                'srp.required' => 'This field is required',
                'srp.numeric' => 'Please enter a valid number',
                'srp.between' => 'Please enter a valid number',
                'dp.required' => 'This field is required',
                'dp.numeric' => 'Please enter a valid number',
                'dp.between' => 'Please enter a valid number',];
                
        $validator = Validator::make($request->all(),[
            'itemcode' => 'required',
            'itemname' => 'required',
            'srp' => 'required|numeric|between:0, 999999999999.999999999999',
            'dp' => 'required|numeric|between:0, 999999999999.999999999999'
        ], $msg);

        if ($validator->fails()) 
        {   
            $errors =['itemcode' => $validator->errors()->get('itemcode'),
                      'itemname' => $validator->errors()->get('itemname'),
                      'dp' => $validator->errors()->get('dp'),
                      'srp' => $validator->errors()->get('srp'),
                      'category' => $validator->errors()->get('category'),
                      'brand' => $validator->errors()->get('brand')];

            return response()->json($errors,200);
        }


        $products = Product::find($request->get('productid'));
        $products->itemcode = $request->get('itemcode');
        $products->itemname = $request->get('itemname');
        $products->avgprice = 0;
        $products->srp = $request->get('srp');
        $products->category = $request->get('category');
        $products->brand = $request->get('brand');
        $products->onhand = 0;
        $products->status = $request->get('status');
        $products->save();

        return response()->json(['success' => 'Record has been updated.'],200);
        // return response()->json($request->all(),200);
    }

    public function deleteproduct($productid)
    {
        $products = Product::find($productid);
        $products->delete();
        return redirect('/product/index')->with('success','Record successfully deleted');
    }

}
