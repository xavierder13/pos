<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['firstname',
            			   'lastname',
			               'phone',
			               'email',
			               'birthdate',
			               'address1',
			               'address2',
			               'city',
			               'state',
			               'postcode'];
}
