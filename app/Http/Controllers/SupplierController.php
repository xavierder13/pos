<?php

namespace App\Http\Controllers;

use App\Supplier;
use Illuminate\Http\Request;
use PragmaRX\Countries\Package\Countries;
use Validator;

class SupplierController extends Controller
{

	public function index()
	{
		$suppliers = Supplier::all();

		return view('pages.supplier.index', compact('suppliers'));
	}

    public function createsupplier()
    {	
    	$countries = Countries::all();
        return view('pages.supplier.create', compact('countries'));
    }

    public function storesupplier(Request $request)
    {	
    	$msg = ['suppliername.required' => 'This field is required'];

    	$validator = Validator::make($request->all(),['suppliername' => 'required'], $msg);

    	if($validator->fails())
    	{
    		$errors = ['suppliername' => $validator->errors()->get('suppliername')];

    		return response()->json($errors, 200);
    	}

    	$suppliers = new Supplier();
    	$suppliers->suppliername = ucfirst($request->get('suppliername')); 
    	$suppliers->contactname = ucfirst($request->get('contactname')); 
    	$suppliers->phone = $request->get('phone'); 
    	$suppliers->email = $request->get('email');
    	$suppliers->fax = $request->get('fax');
    	$suppliers->website = $request->get('website');
    	$suppliers->street = ucfirst($request->get('street'));
    	$suppliers->city = ucfirst($request->get('city'));
    	$suppliers->state = ucfirst($request->get('state'));
    	$suppliers->postcode = $request->get('postcode');
    	$suppliers->country = $request->get('country');
    	$suppliers->save();

    	return response()->json(['success' => 'Record has been added'],200);
    }

    public function editsupplier($supplierid)
    {
    	$suppliers = Supplier::find($supplierid);
    	$countries = Countries::all();
    	return view('pages.supplier.edit', compact('suppliers', 'countries', 'supplierid'));
    }

    public function updatesupplier(Request $request)
    {	
    	$msg = ['suppliername.required' => 'This field is required'];

    	$validator = Validator::make($request->all(),['suppliername' => 'required'], $msg);

    	if($validator->fails())
    	{
    		$errors = ['suppliername' => $validator->errors()->get('suppliername')];

    		return response()->json($errors, 200);
    	}

    	$suppliers = Supplier::find($request->get('supplierid'));
    	$suppliers->suppliername = ucfirst($request->get('suppliername')); 
    	$suppliers->contactname = ucfirst($request->get('contactname')); 
    	$suppliers->phone = $request->get('phone'); 
    	$suppliers->email = $request->get('email');
    	$suppliers->fax = $request->get('fax');
    	$suppliers->website = $request->get('website');
    	$suppliers->street = ucfirst($request->get('street'));
    	$suppliers->city = ucfirst($request->get('city'));
    	$suppliers->state = ucfirst($request->get('state'));
    	$suppliers->postcode = $request->get('postcode');
    	$suppliers->country = $request->get('country');
    	$suppliers->save();

    	return response()->json(['success' => 'Record has been updated'],200);
    }

    public function deletesupplier($supplierid)
    {
    	$suppliers = Supplier::find($supplierid);
    	$suppliers->delete();

    	return redirect('/supplier/index')->with('success', 'Record successfully deleted');
    }
}
