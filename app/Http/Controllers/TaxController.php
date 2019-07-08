<?php

namespace App\Http\Controllers;

use App\Tax;
use Illuminate\Http\Request;
use Validator;
use DataTables;

class TaxController extends Controller
{
    
    public function index()
    {
        // $taxes = Tax::all();    
        return view('pages.tax.index');
    }

    public function getdata()
    {
        $taxes = Tax::all();
        return DataTables::of($taxes)
            ->addColumn('action',function($tax){
                return '<button class="btn btn-xs btn-info" data-toggle="modal" data-target="#modal-tax" data-taxid="'.$tax->id.'" data-modaltitle="Update Tax" data-action="edit" id="btn-edit-tax"><i class="fa fa-pencil"></i> Edit</button> 
                        <button class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-delete" data-taxid="'.$tax->id.'" data-action="delete" id="btn-delete-tax"><i class="fa fa-trash"></i> Delete</button>';
            })
            ->make();
    }

    public function storetax(Request $request)
    {   
        $msg = [
            'taxcode.required' => 'This field is required',
            'taxrate.required' => 'This field is required',
            'taxrate.numeric' => 'Pleas enter a valid number'
        ];

        $validator = Validator::make($request->all(),[
            'taxcode' => 'required',
            'taxrate' => 'required|numeric|between:0, 999999999999.999999999999'
        ], $msg);

        if($validator->fails())
        {   
            $errors = ['taxcode' => $validator->errors()->get('taxcode'),
                       'taxrate' => $validator->errors()->get('taxrate')];
            return response()->json($errors,200);
        }

        $taxes = new Tax();
        $taxes->taxcode = $request->get('taxcode');
        $taxes->taxrate = $request->get('taxrate');
        $taxes->status = $request->get('status');
        $taxes->save();

        $taxes = Tax::all();

        return response()->json(['taxes' => $taxes,'success_store' => 'New tax code has been added'],200);
    }

    public function edittax(Request $request)
    {   
        $taxid = $request->get('taxid');
        $taxes = Tax::find($taxid);

        return response()->json(['taxes' => $taxes, 'taxid' => $taxid], 200);
    }

    public function updatetax(Request $request)
    {   


        $msg = [
            'taxcode.required' => 'This field is required',
            'taxrate.required' => 'This field is required',
            'taxrate.numeric' => 'Pleas enter a valid number'
        ];

        $validator = Validator::make($request->all(),[
            'taxcode' => 'required',
            'taxrate' => 'required|numeric|between:0, 999999999999.999999999999'
        ], $msg);

        if($validator->fails())
        {   
            $errors = ['taxcode' => $validator->errors()->get('taxcode'),
                       'taxrate' => $validator->errors()->get('taxrate')];
            return response()->json($errors,200);
        }

        $taxid = $request->get('taxid');

        $taxes = Tax::find($taxid);
        $taxes->taxcode = $request->get('taxcode');
        $taxes->taxrate = $request->get('taxrate');
        $taxes->status = $request->get('status');
        $taxes->save();

        $taxes = Tax::all();
        
        return response()->json(['taxes' => $taxes, 'success_update' => 'Tax code has been updated'],200);
    }

    public function deletetax(Request $request)
    {   
        $taxid = $request->get('taxid');
        $taxes = Tax::find($taxid);
        $taxes->delete();

        return response()->json(['success' => 'Record has been deleted'],200);
    }

}
