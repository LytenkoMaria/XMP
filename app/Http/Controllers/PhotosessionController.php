<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;

class PhotosessionController extends Controller
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
        return view('photosession');
    }

   public function booking (Request $request)
   {
       $status = Event::createEvent($request);

       return response()->json(["status" => $status]);
   }

    public function get()
    {
        $photosessionEvent = Event::getEvent();

        return response()->json(["photosessionEvent" => $photosessionEvent]);
    }

    public function change(Request $request)
    {
        $status = Event::changeEvent($request);

        return response()->json(["status" => $status]);
    }

    public function getChoseXMP(Request $request)
    {
        $choseXMP = Event::getChoseXMP($request);

        return response()->json(["choseXMP" => $choseXMP]);
    }


}
