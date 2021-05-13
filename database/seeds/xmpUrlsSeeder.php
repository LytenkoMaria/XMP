<?php

use Illuminate\Database\Seeder;
use App\xmpUrl;

class xmpUrlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prefixs = array(

array('prefix' => "photoshop", 	    'url' => "http://ns.adobe.com/photoshop/1.0/",            'name' => "Photoshop"),
array('prefix' => "xmpRights", 	    'url' => "http://ns.adobe.com/xap/1.0/rights/",           'name' => "XMP Rights Management"),
array('prefix' => "dc", 			'url' => "http://purl.org/dc/elements/1.1/",              'name' => "Dublin Core"),
array('prefix' => "xmp", 			'url' => "http://ns.adobe.com/xap/1.0/",                  'name' => "XMP Basic"),
array('prefix' => "photomechanic",  'url' => "http://ns.camerabits.com/photomechanic/1.0/",   'name' => ""),
array('prefix' => "Iptc4xmpCore", 	'url' => "http://iptc.org/std/Iptc4xmpCore/1.0/xmlns/",   'name' => "IPTC Core"),
array('prefix' => "Iptc4xmpExt", 	'url' => "http://iptc.org/std/Iptc4xmpExt/2008-02-29/",   'name' => "IPTC Extension"),
array('prefix' => "plus", 			'url' => "http://ns.useplus.org/ldf/xmp/1.0/",            'name' => "PLUS License Data Format"),
array('prefix' => "aux", 			'url' => "http://ns.adobe.com/exif/1.0/aux/",        'name' => "Exif Schema for Additional Exif Properties"),
array('prefix' => "exif", 			'url' => "http://ns.adobe.com/exif/1.0/",              'name' => "Exif Schema for Exif-specific Properties"),
array('prefix' => "tiff", 			'url' => "http://ns.adobe.com/tiff/1.0/",                 'name' => "Exif Schema for TIFF Properties"),
array('prefix' => "xap", 			'url' => "http://ns.adobe.com/xap/1.0/",                  ),
array('prefix' => "stRef", 		    'url' => "http://ns.adobe.com/xap/1.0/sType/ResourceRef#",),
array('prefix' => "xapMM", 		    'url' => "http://ns.adobe.com/xap/1.0/mm/",               ),

    	);
    	xmpUrl::insert($prefixs);
    }
}
