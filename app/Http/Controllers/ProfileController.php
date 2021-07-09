<?php

namespace App\Http\Controllers;

use App\Event;
use App\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile');
    }

    public function NewProfile (Request $request)
    {
        $status = Profile::createProfile($request);

        return response()->json([ "status" => $status ]);
    }

    public function GetProfile ()
    {
        $profile = Profile::GetProfile();

        return response()->json([ "profile" => $profile ]);
    }
}
