<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PhpParser\Node\Scalar\String_;

class XMP extends Authenticatable
{
    use Notifiable;

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
        //dd($xmp);
       /* $success = self::create([
            'xmp' => $xmp,
        ]);

        return response()->json(["status" => $success]);*/
    }


}
