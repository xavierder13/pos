<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'itemcode',
        'itemname',
        'category',
        'brand',
        'avgprice',
        'srp',
        'tax',
        'onhand',
        'status'
    ];
}
