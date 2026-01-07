<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    public function health()
    {
        return $this->successResponse();
    }
}
