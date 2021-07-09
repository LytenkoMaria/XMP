<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Directory extends Model
{
    protected $table = 'gallery_directory';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','user_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    public static function createDirectory($request)
    {
        $chek = self::where('name', '=', $request['name'])
        ->where('user_id', '=', Auth::user()->id)
        ->exists();

        if (!$chek) {

            $success = self::create([
                'name' => $request['name'],
                'user_id' => Auth::user()->id,
            ]);

            return true;

        }else {

            return false;

        }
    }

    public static function GetDirectory()
    {
            $directory = self::where('user_id', Auth::user()->id)->get();

            return $directory;

    }
}
