<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class xmpTag extends Model
{
   	 protected $fillable = [
        'name', 'label', 'prefix', 'type', 'valueType', 'category', 'description', 'bind', 'readonly', 'enumeration'
    ];

    public static function getExternal($prefix) {
    	$result = xmpTag::where('prefix', $prefix)
                         ->where('category', 'External')
                         -> get();
        foreach ($result as $key => $value) {
        	//if (isset($value['enumeration']) && count($value['enumeration'])) {
        		$result[$key]['enumeration'] = json_decode($value['enumeration']);
        	//}
        }
        return $result;
    }

}
