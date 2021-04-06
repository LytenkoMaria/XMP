<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ChangeXmp extends Model
{
    protected $table = 'change_xmp';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'xmp',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    public static function createChangeXMP($xmp)
    {
        //dd('123');
        //dd($xmp["contents"]);
       /* var_dump($xmp["contents"]); die();
         $success = self::create([
             'xmp' => $xmp["contents"],
         ]);

         return response()->json(["status" => $success]);*/
    }

}
