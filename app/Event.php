<?php

namespace App;

use http\Env\Request;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title','start_date','id_xmp'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    public function xmp()
    {
        return $this->hasOne(XMP::class, 'id', 'id_xmp');
    }

    public static function createEvent($request)
    {
         $success = self::create([
             'start_date' => $request['data'],
             'title' => $request['title'],
             'id_xmp' => $request['id_xmp'],
         ]);

         return response()->json(["status" => $success]);
    }

    public static function getEvent()
    {
        $event = self::select('title','start_date as date', 'id')->get();
        $i=0;

        foreach ($event as $ev) {
            $event[$i]->color =  '#007bffb5';//'#7db8f5';// '#007bffb5';
            ++$i;
        }

        return $event;
    }

    public static function changeEvent($request)
    {
        $success = self::where('id', $request['id'])
        ->update(['start_date' => $request["data"], 'title'=> $request["title"], 'id_xmp'=> $request["id_xmp"]]);

        return $success;
    }

    public static function getChoseXMP($request)
    {
        $success = self::find($request->get('id'));

        return $success->xmp->id;
    }

}
