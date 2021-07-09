<?php

namespace App\Http\Controllers;

use App\Directory;
use App\Profile;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function NewDirectory (Request $request)
    {
        $status = Directory::createDirectory($request);

        return response()->json([ "status" => $status ]);
    }

    public function GetDirectory ()
    {
        $directories = Directory::getDirectory();

        return response()->json([ "directories" => $directories ]);
    }
}
