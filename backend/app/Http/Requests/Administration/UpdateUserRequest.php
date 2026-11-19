<?php

namespace App\Http\Requests\Administration;

// same fields as create; password optional (blank = unchanged), active required
class UpdateUserRequest extends StoreUserRequest {}
