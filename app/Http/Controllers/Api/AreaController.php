<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Cache;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function show(Area $area)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function edit(Area $area)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Area $area)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function destroy(Area $area)
    {
        //
    }

    /**
     * 全国省份列表
     * @param  Request  $request
     * @return mixed
     */
    public function provinceList(Request $request)
    {
        if (Cache::has('area_province_data')) {
            $data = Cache::get('area_province_data');
        } else {
            $data = Area::where('level', 1)
                ->select('id', 'pid', 'name', 'level')
                ->get();
            Cache::put('area_province_data', $data, 3600 * 24 * 3650);
        }

        return $data;
    }

    /**
     * 某省份包含的市列表
     * @param  Request  $request
     * @return mixed
     */
    public function cityList(Request $request)
    {

        $key = 'area_data_city_'.$request->id;
        if (Cache::has($key)) {
            $data = Cache::get($key);
        } else {
            $data = Area::where('pid', '=', $request->id)
                ->where('level', '=', 2)
                ->select('id', 'pid', 'name', 'level')
                ->get();
            Cache::put($key, $data, 3600 * 24 * 3650);
        }

        return $data;
    }

    /**
     * 某市包含的区列表
     * @param  Request  $request
     * @return mixed
     */
    public function districtList(Request $request)
    {
        $key = 'area_data_district_'.$request->id;
        if (Cache::has($key)) {
            $data = Cache::get($key);
        } else {
            $data = Area::where('pid', '=', $request->id)
                ->where('level', '=', 3)
                ->select('id', 'pid', 'name', 'level')
                ->get();
            Cache::put($key, $data, 3600 * 24 * 3650);
        }

        return $data;
    }

    /**
     * 全国省市区列表
     * @param  Request  $request
     * @return array|mixed
     */
    public function areaList(Request $request)
    {
        // province_id', 'city_id', 'district_id
        if (Cache::has('area_data')) {
            $data = Cache::get('area_data');
        } else {
            $data = Area::where('level', 1)
                ->select('id', 'pid', 'name', 'level')
                ->get();
            foreach ($data as $key => $value) {
                $citys = Area::where('pid', '=', $value->id)
                    ->where('level', '=', 2)
                    ->select('id', 'pid', 'name', 'level')
                    ->get();
                foreach ($citys as $k => $v) {
                    $district = Area::where('pid', '=', $v->id)
                        ->where('level', '=', 3)
                        ->select('id', 'pid', 'name', 'level')
                        ->get();
                    $citys[$k]['district'] = $district;
                }
                $data[$key]['city'] = $citys;
            }
            Cache::put('area_data', $data, 3600 * 24 * 3650);
        }

        return $data;
    }
}
