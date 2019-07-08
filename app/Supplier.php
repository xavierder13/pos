<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable =[ 'suppliername',
				            'contactname',
				            'phone',
				            'email',
				            'fax',
				            'website',
				            'street',
				            'city',
				            'state',
				            'postcode',
				            'country'];
}
