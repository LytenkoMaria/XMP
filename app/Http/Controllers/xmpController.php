<?php

namespace App\Http\Controllers;

use App\ChangeXmp;
use Illuminate\Http\Request;
use App\xmpTag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\XmpParser;
use App\xmpUrl;
use SplFileInfo;
use function Couchbase\defaultDecoder;

class xmpController extends Controller
{
    private $content = [];
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
        return view('xmp');
    }
    public function getXMP(Request $request)
    {
        $xmpDataExtractor = new XmpParser();
        $xmpData = $xmpDataExtractor->extractFromFile($request->file('attachment'));
        $token = Str::random(10);
        $image = $request->file('attachment');
        $imageName = $image->getClientOriginalName();
        $newName = $token.$imageName;
        $image->move(public_path() . '/images/ChangeXMP/',$newName);
        return json_encode( ['extractXMP' => $xmpData, 'imageName' => $newName]);
    }

    public function setXMP(Request $request)
    {
        $ImageName = $request['changeImageName'];
        $file = new SplFileInfo(public_path() . '/images/ChangeXMP/'.$ImageName);
        $contents = file_get_contents($file->getPathname());

        $input = $request ->get('xmp');
        $xmpString = 'Unknown format';

        if (isset($input['x:xmpmeta'])) {
            $xmpString = '<x:xmpmeta xmlns:x="adobe:ns:meta/"';
            $data = $input['x:xmpmeta'];

            //print_r($data);
            if(isset($data['@attributes'])) {
                $xmpString.= $this->getArrtibutes($data['@attributes']);
            }

            $xmpString.= '>';
            $xmpString.= '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">';

            $tags = false;
            if (isset($input['tags'])) {
                $tags = $input['tags'];
            }
            $xmpString.= $this->handle($data, false, $tags);
            $xmpString.= "</rdf:RDF></x:xmpmeta>";
        }
        //dd($xmpString);

        $xmpDataStart = strpos($contents, '<x:xmpmeta');
        $xmpDataEnd = strpos($contents, '</x:xmpmeta>');
        $xmpLength = $xmpDataEnd - $xmpDataStart;
        $lastXMP = substr($contents, $xmpDataStart, $xmpLength + 12);
         //dd($lastXMP);
        $description = stristr($lastXMP, '<rdf:Description rdf:about=""');
        //$description=trim($description, "\n");
        $description = str_replace("\n", ' ', $description);
        $description = str_replace("    ", ' ', $description);
        //dd($description);
        $description = stristr($description, '>', true);
        $pattern = '~xmlns:.*/"~';
        $xmlns= null;
        preg_match_all ( $pattern , $description , $xmlns );
        //dd($xmlns);
        $newDescription = '<rdf:Description rdf:about="" '.$xmlns[0][0];
        //dd($newDescription);
        $newXmpString = str_replace('<rdf:Description rdf:about=""',$newDescription, $xmpString);
        //substr_replace($xmpString, '$xmlns', 0);
        //dd($newXmpString );
        //dd($description);
        //dd("new",$newXmpString,"last",$lastXMP);
        //dd("last",$lastXMP);
        $xmpDataExtractor = new XmpParser();
        $x = file_get_contents('images/last.txt');
        $ImageName = $request['changeImageName'];
        $file = new SplFileInfo(public_path() . '/images/ChangeXMP/'.$ImageName);
        $contents = file_get_contents($file->getPathname());
        $x=trim($x, " \n");

        //dd("new",$x,"last",$lastXMP);
//        dd($newXmpString);
        //dd($newXmpString);
        $newphrase = str_replace($lastXMP,$newXmpString, $contents);
        $xmpDataStart = strpos($newphrase, '<x:xmpmeta');
        $xmpDataEnd = strpos($newphrase, '</x:xmpmeta>');
        $xmpLength = $xmpDataEnd - $xmpDataStart;
        $last = substr($newphrase, $xmpDataStart, $xmpLength + 12);
//        dd($last);
         //dd($lastXMP);
        $f=fopen(public_path() . '/images/ChangeXMP/'.$ImageName,'w');

        $e=fwrite($f,$newphrase);
        //dd($e);
        fclose($f);
        file_put_contents('images/test.txt', $newphrase);
        var_dump(file_get_contents('images/test.txt')); die();
        //dd($last);
        $file = new SplFileInfo(public_path() . '/images/ChangeXMP/'.$ImageName);
        $contents = file_get_contents($file->getPathname());
        $xmpDataStart = strpos($contents, '<x:xmpmeta');
        $xmpDataEnd = strpos($contents, '</x:xmpmeta>');
        $xmpLength = $xmpDataEnd - $xmpDataStart;
        $c = substr($newphrase, $xmpDataStart, $xmpLength + 12);
        dd('change',$c);

        /*if (fwrite($file, $newphrase) === FALSE) {
            dd('aaaaa');
        }
        */
        //dd(file_put_contents($file, $newphrase));
       // file_put_contents($file, $newphrase);

      /* if(file_put_contents($file, $newphrase)==false)
       {
           dd($x);
       }*/

/*
        $ImageName = $request['changeImageName'];
        $file = new SplFileInfo(public_path() . '/images/ChangeXMP/'.$ImageName);
        $contents = file_get_contents($file->getPathname());
        $xmpDataStart = strpos($contents, '<x:xmpmeta');
        $xmpDataEnd = strpos($contents, '</x:xmpmeta>');
        $xmpLength = $xmpDataEnd - $xmpDataStart;
        $last = substr($contents, $xmpDataStart, $xmpLength + 12);
       dd('aa',$last);
        return ($xmpString);*/
    }

    private function handle($xmp, $header = false, $tags = false)
    {
        $line = "";
        $arrtEx = false;
        if (is_array($xmp) && array_key_exists("@attributes", $xmp) && $header) {
            $arrtEx = count($xmp) === 1;
            $line.= $this->getArrtibutes($xmp["@attributes"], false, "<".$header, $arrtEx);
        }
        else {
            if ($header === "rdf:Description") {
                if ($tags) {
                    foreach ($tags as $key => $value) {
                        $tag = xmpUrl::where('prefix', $value)->first();
                        if ($tag) {
                            $line.= " xmlns:". $tag->prefix.'="'.$tag->url.'"';
                        }
                    }
                    $line.= ">";
                }
            }
        }

        $arrFind = true;
        if (is_array($xmp) && $header) {
            if (array_key_exists(XmpParser::RDF_ALT, $xmp) && is_array($xmp[XmpParser::RDF_ALT])) {
                $alt = $xmp[XmpParser::RDF_ALT];
                if (array_key_exists("@attributes", $alt)) {
                    $line.= "<".XmpParser::RDF_ALT.">";
                    if (array_key_exists("@content", $alt) && is_array($alt['@content'])) {
                        //TODO: Вроде, такого быть не может, но, на всякий случай, надо сделать
                    }
                    else {
                        $line.= $this->getArrtibutes($alt['@attributes'], false, "<". XmpParser::RDF_LI);
                        if (array_key_exists("@content", $alt)) {
                            $line.= $alt['@content'];
                        }
                        $line.= "</".XmpParser::RDF_LI.">";
                    }
                    $line.="</".XmpParser::RDF_ALT.">";
                }
            }
            elseif (array_key_exists(XmpParser::RDF_SEQ, $xmp)) {
                $line.= "<".XmpParser::RDF_SEQ.">";

                if (is_array($xmp[XmpParser::RDF_SEQ])) {
                    $arrtEx_ = false;
                    foreach ($xmp[XmpParser::RDF_SEQ] as $key => $value) {

                        if ($key === "@attributes") {
                            $arrtEx_ = count($xmp[XmpParser::RDF_SEQ]) === 1;

                            if ($arrtEx_) {
                                $line.= $this->getArrtibutes($value, false, "<".XmpParser::RDF_LI, $arrtEx_);
                            }
                        }
                        elseif (is_array($value)) {
                            if (array_key_exists("@attributes", $value)) {

                                $arrtEx_ = count($value) === 1;

                                if ($arrtEx_) {
                                    $line.= $this->getArrtibutes($value['@attributes'], false, "<".XmpParser::RDF_LI, $arrtEx_);
                                }
                                else {
                                    $line.= "<".XmpParser::RDF_LI."> ";
                                    $line.= $this->handle($value, $key);
                                }
                            }
                            else {
                                //handle ??
                            }
                        }
                        else {
                            $line.= "<". XmpParser::RDF_LI.">";
                            $line.= $value;
                        }
                        if (!$arrtEx_) $line.= "</".XmpParser::RDF_LI.">";
                    }

                }
                else {
                    $line.= "<". XmpParser::RDF_LI.">" .$xmp[XmpParser::RDF_SEQ] . "</".XmpParser::RDF_LI.">";
                }


                $line.= "</".XmpParser::RDF_SEQ.">";
            }
            elseif (array_key_exists(XmpParser::RDF_BAG, $xmp)) { //RDF_BAG
                $line.= "<".XmpParser::RDF_BAG.">";

                if (is_array($xmp[XmpParser::RDF_BAG])) {
                    foreach ($xmp[XmpParser::RDF_BAG] as $key => $value) {

                        $arrtEx_ = false;

                        if ($key === "@attributes") {
                            $arrtEx_ = count($value) === 1;
                            $line.= $this->getArrtibutes($value, false, "<".XmpParser::RDF_LI, $arrtEx_);
                        }
                        elseif (is_array($value)) {
                            if (array_key_exists("@attributes", $value)){
                                $arrtEx_ = count($value) === 1;
                                if ($arrtEx_) {
                                    $line.= $this->getArrtibutes($value['@attributes'], false, "<".XmpParser::RDF_LI, $arrtEx_);
                                }
                                else {
                                    $line.= "<".XmpParser::RDF_LI."> ";
                                    $line.= $this->handle($value, $key);
                                }
                            }
                            else {
                                //handle?
                            }
                        }
                        else {
                            $line.= "<".XmpParser::RDF_LI.">";
                            $line.= $value;
                        }

                        if (!$arrtEx_) $line.= "</".XmpParser::RDF_LI.">";
                    }
                }
                else {
                    $line.= "<".XmpParser::RDF_LI.">";
                    $line.= $xmp[XmpParser::RDF_BAG];
                    $line.= "</".XmpParser::RDF_LI.">";
                }
                $line.="</".XmpParser::RDF_BAG.">";
            }
            else {
                $arrFind = false;
            }
        }
        else {
            $arrFind = false;
        }

        if (!$arrFind) {
            if (is_array($xmp)) {
                foreach ($xmp as $key => $value) {
                    if ($key === "@attributes") {
                        continue;
                    }

                    if (is_array($value)) {
                        if ($this->has_string_keys($value) && !array_key_exists("@content", $value)) {
                            if (!array_key_exists("@attributes", $value)) {
                                $line.= "<".$key;
                                if ($key !== "rdf:Description") {
                                    $line.= ">";
                                }
                            }
                            $line.= $this -> handle($value, $key, $tags);
                        }
                        else {
                            foreach ($value as $key_ => $value_) {
                                $line.=$this -> handle($value_, $key);
                            }
                        }
                        //$line.= $this -> handle($value, $key);
                    }
                    else {
                        //$line.= $key.":".$value;
                        $line.= "<".$key.">".$value."</".$key.">";
                    }
                }
            }
        }

        if ($header && !$arrtEx) {
            $line.= "</".$header.">";
        }
        return $line;
    }

    private function has_string_keys(array $array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    private function getArrtibutes($attr, $prefix = false, $header = false, $close = false) : string
    {
        $line = "";

        if ($header) {
            $line.= $header;
        }

        foreach ($attr as $key => $value) {
            if (!is_array($value)) {
                if ($prefix) {
                    $line.= " ".$prefix. ":";
                }
                $line.= $key.'="'.$value.'"';
            }
            else {
                $line.= $this->getArrtibutes($value, $key);
            }
        }

        if ($header) {
            if ($close) {
                $line.= '/';
            }
            $line.= '>';
        }

        return $line;
    }

    public function showXMP (Request $request)
    {
        $tegs = [];
        $xmpTegs = xmpTag::where('category', 'External')->get();
        foreach ($xmpTegs as $value) {
            $prefix = $value["prefix"];
            $name = $value["name"];
            $tegs[] = $prefix . ":" . $name;
        }
        $same = [];
        $masName = [];
        $data = $request['Description'];
        $nameData = array_keys($data);

        foreach ($nameData as $valueD) {
            foreach ($tegs as $valueT) {
                if (strcmp($valueD, $valueT) == 0) {

                    $a = $data[$valueT];
                    $same[] = ['name' => $valueT, 'data' => $a];
                }
            }
        }
          $i = 0;
            foreach ($same as $valu) {
                if (strcmp($valu['name'], 'Iptc4xmpCore:CreatorContactInfo') == 0) {
                    unset($same[$i]);
                    $masName =  array_keys($valu['data']['@attributes']['Iptc4xmpCore']);
                    foreach ($masName as $val) {
                        $dataCreator = $valu['data']['@attributes']['Iptc4xmpCore'][$val];
                        $n = "Iptc4xmpCore:" . $val;
                        $same[] = ['name' => $n, 'data' => $dataCreator];
                    }
                }$i++;
            }

        $resultList = [];
        $list = xmpUrl::select('name','prefix') -> get();
        foreach ($list as $key => $value) {
            $tags[] = xmpTag::getExternal($value['prefix']);
            $resultList[$key] = $value;
            $resultList[$key]['list'] = $tags;
        }
        $result=[];
        foreach ($resultList as $value)
        {
            $masresultList[]=$value;
        }
        foreach ($masresultList as $val)
        {
            foreach ($val['list'] as $vals) {
            }
            foreach ($vals as $valui) {
                foreach ($same as $vln) {
                    $n = substr(strrchr($vln['name'], ":"), 1);
                    if($valui['name'] == $n)
                    {
                        $valui['append'] = false;
                        if(strcmp($valui["prefix"], "Iptc4xmpCore") == 0)
                        {
                            $text[] = $vln['data'];
                            $valui['text'] = $text;
                            $text = [];
                        }
                        else {
                            $atr = array_keys($vln['data']);
                            if ($atr[0] == 'rdf:Alt') {
                                $text[] = $vln['data']['rdf:Alt']['@content'];
                                $valui['text'] = $text;
                                $text = [];
                            }
                            if ($atr[0] == 'rdf:Bag') {
                                $valui['text'] = $vln['data']['rdf:Bag'];
                            }
                            if ($atr[0] == 'rdf:li') {
                                $text[] = $vln['data']['rdf:li'];
                                $valui['text'] = $text;
                                $text = [];
                            }
                            if ($atr[0] == 'rdf:Seq') {
                                $Seq[] = $vln['data']['rdf:Seq'];
                                $valui['text'] = $Seq;
                            }

                        }$result[] = $valui;
                    }
                }
            }
        }
       // $data = $request['xmp'];
       // dd($data);



       /* $list = $request['list'];
        //dd($list);
        foreach ($list as $value) {
            $d['name'] = $value['name'];
            $d['prefix'] = $value['prefix'];
            foreach ($value['list'] as $val) {

                foreach ($result as $v) {
                    }
                    if (strcmp($val['name'],$v['name'])!=0) {
                        $arr[] = $val;
                        $d['list'] = $arr;
                    }

                //dd($d);

            }
            $newList[] = $d;
        }*/
       // dd(response()->json( ['List2' => $result, 'Lists' => $newList ] ));
        //dd($result);
        return response()->json( ['list2' => $result ]); //, 'lists' => $newList ] );
    }

    public function changeXMP (Request $request)
    {
        //dd($request);
      // $newText = $request['item']['newText'];
        //dd($request['XMPforChange']);
       //$itemName = $request['item']['prefix'] . ':' . $request['item']['name'];
        $itemName = $request['item'];
        //dd($itemName);
       $changeXMP = $request['XMPforChange']['x:xmpmeta']['rdf:Description'];
        $xmpName = array_keys($changeXMP);
        foreach ($xmpName as $value) {
            if (strcmp($value, "Iptc4xmpCore:CreatorContactInfo") == 0)
            {
                $mpCore = array_keys($changeXMP[$value]["@attributes"]["Iptc4xmpCore"]);
            }
            if (isset($mpCore)) {
                foreach ($mpCore as $core) {
                    foreach ($itemName as $item){
                        if(strcmp($core,$item['item']['name']) == 0) {
                            $changeXMP["Iptc4xmpCore:CreatorContactInfo"]["@attributes"]["Iptc4xmpCore"][$core]= $item["newText"][0]["text"];
                        }
                    }
                }
            }
            foreach ($itemName as $val) {
                $valName = $val['item']["prefix"].":".$val['item']['name'];
                if (strcmp($value,$valName) == 0) {

                    $atr = array_keys($changeXMP[$value]);
                    if ($atr[0] == 'rdf:Alt') {
                        $changeXMP[$value]['rdf:Alt']['@content'] = $val["newText"][0]["text"];
                    }
                    if ($atr[0] == 'rdf:Bag') {
                        foreach ( $val["newText"]as $bag) {
                            $bags[] = $bag["text"];
                        }
                        $changeXMP[$value][$atr[0]] = $bags;
                        $bags = [];
                    }
                    if ($atr[0] == 'rdf:li') {
                        foreach ( $val["newText"]as $bag) {
                            $bags[] = $bag["text"];
                        }
                        $changeXMP[$value][$atr[0]] = $bags;
                        $bags = [];
                    }
                    if ($atr[0] == 'rdf:Seq') {
                        foreach ( $val["newText"]as $bag) {
                            $bags[] = $bag["text"];
                        }
                        $changeXMP[$value][$atr[0]] = $bags;
                        $bags = [];
                    }
                }
            }
        }
        $newXMP = $request['XMPforChange'];
        $newXMP['x:xmpmeta']['rdf:Description'] = $changeXMP;
        return response()->json( ['newXMP' => $newXMP ]);

        /*$a ="aaaaaaaaaaaabbbbbbbbbbbbdddddddddddd";
        $b ="bbbbbbbbbbbb";
        $bodytag = str_replace( $b, "black",$a);
        dd($bodytag);*/

    }
}
