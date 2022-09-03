<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    //
    public function test()
    {
        $id = 11;
        $area = getAreaName($id);
        $data['area'] = $area;
        return $data;
    }
}
