<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\xmpUrl;
use App\xmpTag;

class DnDController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $list = xmpUrl::select('name','prefix') -> get();
        $resultList = [];
        foreach ($list as $key => $value) {
            $tags = xmpTag::getExternal($value['prefix']);
                $resultList[$key] = $value;
                $resultList[$key]['list'] = $tags;
        }
        return view('drag_and_drop', ["lists" => $resultList]);
    }


}
