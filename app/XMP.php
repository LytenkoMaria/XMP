<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PhpParser\Node\Scalar\String_;

class XMP extends Authenticatable
{
    use Notifiable;

    protected $table = 'new_xmp';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'user_id', 'name_xmp', 'label_file' ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    public static function createXMP($request)
    {
        $success = self::create([
            'user_id' => $request['user_id'],
            'name_xmp' => $request['name_xmp'],
            'label_file' => $request['label'],
        ]);

        

        return response()->json(["status" => $success]);
    }


}
