<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function createpo()
    {

    	return view('pages.po.create');
    }
}
