<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
class CustomerController extends Controller
{
    
    public function index()
    {   
        $customers = Customer::all();

        return view('pages.customer.index', compact('customers'));
    }

    
    public function createcustomer()
    {
        return view('pages.customer.create');
    }

    
    public function storecustomer(Request $request)
    {   
        $msg = ['firstname.required' => 'This field is required',
                'lastname.required' => 'This field is required'];

        $validator = Validator::make($request->all(),[
            'firstname' => 'required',
            'lastname' => 'required'
        ], $msg);

        if($validator->fails())
        {
            $errors = ['firstname' => $validator->errors()->get('firstname'),
                       'lastname' => $validator->errors()->get('lastname')];

            return response()->json($errors, 200);
        }

        
        $customers = new Customer();
        $customers->firstname = ucfirst($request->get('firstname'));
        $customers->lastname = ucfirst($request->get('lastname'));
        $customers->phone = $request->get('phone');
        $customers->email = $request->get('email');
        $customers->birthdate = Carbon::parse($request->get('birthdate'))->format('y-m-d');
        $customers->address1 = ucfirst($request->get('address1'));
        $customers->address2 = ucfirst($request->get('address2'));
        $customers->city = ucfirst($request->get('city'));
        $customers->state = ucfirst($request->get('state'));
        $customers->postcode = $request->get('postcode');
        $customers->save();

        return response()->json(['success' => 'Record has been added'], 200);

        // return response()->json(Carbon::parse($request->get('birthdate'))->format('Y-m-d'), 200);

    }

        
    public function editcustomer($customerid)
    {
        $customers = Customer::find($customerid);
        $birthdate = date('m/d/Y',strtotime($customers->birthdate));

        return view('pages.customer.edit', compact('customers', 'customerid', 'birthdate'));
    }

    
    public function updatecustomer(Request $request)
    {
        $msg = ['firstname.required' => 'This field is required',
                'lastname.required' => 'This field is required'];

        $validator = Validator::make($request->all(),[
            'firstname' => 'required',
            'lastname' => 'required'
        ], $msg);

        if($validator->fails())
        {
            $errors = ['firstname' => $validator->errors()->get('firstname'),
                       'lastname' => $validator->errors()->get('lastname')];

            return response()->json($errors, 200);
        }

        
        $customers = Customer::find($request->get('customerid'));
        $customers->firstname = ucfirst($request->get('firstname'));
        $customers->lastname = ucfirst($request->get('lastname'));
        $customers->phone = $request->get('phone');
        $customers->email = $request->get('email');
        $customers->birthdate = Carbon::parse($request->get('birthdate'))->format('y-m-d');
        $customers->address1 = ucfirst($request->get('address1'));
        $customers->address2 = ucfirst($request->get('address2'));
        $customers->city = ucfirst($request->get('city'));
        $customers->state = ucfirst($request->get('state'));
        $customers->postcode = $request->get('postcode');
        $customers->save();

        return response()->json(['success' => 'Record has been updated'], 200);
    }

    
    public function deletecustomer($customerid)
    {
        $customers = Customer::find($customerid);
        $customers->delete();

        return redirect('/customer/index')->with('success', 'Record successfully deleted');
    }
}
