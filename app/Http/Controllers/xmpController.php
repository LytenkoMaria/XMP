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
use DOMDocument;
use function Couchbase\defaultDecoder;

class xmpController extends Controller
{
    private $a=[];
    private $o=[];

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

    public function setNewXMP(Request $request)
    {
        $input = $request -> all();
        $xmpString = 'Unknown format';

        if (isset($input['x:xmpmeta'])) {
            $xmpString = '<x:xmpmeta xmlns:x="adobe:ns:meta/"';
            $data = $input['x:xmpmeta'];

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

        print_r($xmpString);
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

            if(isset($data['@attributes'])) {
                $xmpString.= $this->getArrtibutes($data['@attributes']);
            }
            $xmpString.= '> ';
            $xmpString.= '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"> ';
            $tags = false;
            if (isset($input['tags'])) {
                $tags = $input['tags'];
            }
            $xmpString.= $this->handle($data, false, $tags);
            $xmpString.= " </rdf:RDF> </x:xmpmeta>";
        }
        $xmpDataStart = strpos($contents, '<x:xmpmeta');
        $xmpDataEnd = strpos($contents, '</x:xmpmeta>');
        $xmpLength = $xmpDataEnd - $xmpDataStart;
        $lastXMP = substr($contents, $xmpDataStart, $xmpLength + 12);
        $description = stristr($lastXMP, '<rdf:Description rdf:about=""');
        $description = str_replace("\n", '', $description);
        $description = str_replace("    ", ' ', $description);
        $description = str_replace("   ", ' ', $description);
        $description = stristr($description, '>', true);
        $pattern = '~xmlns:.*/"~';
        $xmlns= null;
        preg_match_all ( $pattern ,$description, $xmlns );
        $newXmlns = str_replace('xmlns',' xmlns', $xmlns[0][0]);
        $newDescription = "\n".'<rdf:Description rdf:about=""'."\n".$newXmlns;
        $newXmpString = str_replace('<rdf:Description rdf:about=""',$newDescription, $xmpString);
        $newXmpString = str_replace('><','> <', $newXmpString);
        $newXmpString = str_replace('> <','>'."\n".'<', $newXmpString);
        $pattern = '~xmlns:.*">~';
        $trimDescription= null;
        preg_match_all ( $pattern ,$newXmpString, $trimDescription );
        $enterDescription = str_replace('" ','"'."\n", $trimDescription[0][2]);
        $newXmpString = str_replace($trimDescription[0][2],$enterDescription, $newXmpString);
        $i = 0;
        $lines_count = substr_count($newXmpString , "\n") + 1;
        $pattern = "~.*\n~";
        $trimString = null;
        preg_match_all ( $pattern ,$newXmpString, $trimString );
        $new=null;
        foreach ($trimString[0] as $val) {
            if ($i == 1) {
                $new=$new.' '.$val;
            }
            if ($i == 2) {
                $new=$new.'  '.$val;
            }
            if ($i > 2) {
                $new=$new.'   '.$val;
            }
            $i++;
        }
        $a = $this->checkIndex($trimString[0]);
        $n=null;$i = 0;
        foreach ($a as $v) {
            if ($i < 2) {
                $n=$n.$v;
            }
            if ($i == 2) {
                $n=$n.' '.$v;
            }
            if ($i > 2) {
                  $n=$n.'   '.$v;
            }
            ++$i;
        }
        $nord=$n.'</x:xmpmeta>';
        $trimString = null;
        preg_match_all ( $pattern ,$new, $trimString );
        $pattern = "~<.*~";
        $tr = null;
        preg_match_all ( $pattern ,$newXmpString, $tr );
        for ($i=2; $i<$lines_count-2; $i++) {
            $mass['start'][] = $i;
            $trim = stristr($trimString[0][$i],'>',true);
            $mass['atr'][] = $trim;
        }

        $mass = [ 'atr','start', 'end'];
        foreach ( $trimString[0] as $val) {
            if ($i > 2) {
                $mass['start'][] = $i;
                $trim = stristr($val,'>',true);
                $mass['atr'][] = $trim;
            }
            $i++;
        }

        $ImageName = $request['changeImageName'];
        $file = new SplFileInfo(public_path() . '/images/ChangeXMP/'.$ImageName);
        $contents = file_get_contents($file->getPathname());
        $newphrase = str_replace($lastXMP,$nord, $contents);
        $f=fopen(public_path() . '/images/ChangeXMP/'.$ImageName,'w');
        fwrite($f,$newphrase);
        fclose($f);

         //$xmpDataExtractor = new XmpParser();
        //$x = file_get_contents('images/last.txt');
        //$x = trim($x, " \n");
        /*
         * //$contents.= $newXmpString;
        //$xmpDataStart = strpos($newphrase, '<x:xmpmeta');
        //$xmpDataEnd = strpos($newphrase, '</x:xmpmeta>');
        //$xmpLength = $xmpDataEnd - $xmpDataStart;
        //$last = substr($newphrase, $xmpDataStart, $xmpLength + 12);
        file_put_contents('images/test.txt', $newphrase);
        $file = new SplFileInfo(public_path() . '/images/ChangeXMP/'.$ImageName);
        $contents = file_get_contents($file->getPathname());
        $xmpDataStart = strpos($contents, '<x:xmpmeta');
        $xmpDataEnd = strpos($contents, '</x:xmpmeta>');
        $xmpLength = $xmpDataEnd - $xmpDataStart;
        $c = substr($contents, $xmpDataStart, $xmpLength + 12);
        dd('change',$c);*/

    }

    private function checkTag($str)
    {
        if (preg_match("~.*<.*>.*</~", $str)) {
            return 0;
        }
        if(!preg_match("~^<.*>$~", $str)) {
            return 0;
        }
        if(preg_match("~^</.+>~", $str))  {
            $this->a[]=$str;
            return -1;
        }

        return 1;
    }

    public function checkIndex($mass_str)
    {
        $sp = "";
        foreach ($mass_str as $index => $str)
        {
            $str = trim($str, " ");
            $tag = $this->checkTag($str);
            $mass_str[$index] = $sp.$str;
            if($tag === 1) {
                $sp .= " ";
            }
            else if($tag === -1){
                $sp = mb_substr($sp, 0, -1);
            }
        }

        return $mass_str;
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
                            $this->o[]=$value;
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
                                foreach ($value as $val){
                                $line.= "<".XmpParser::RDF_LI.">";
                                $line.=$val;
                                $line.= "</".XmpParser::RDF_LI.">";
                                    $arrtEx_ = true;
                                }
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
                                foreach ($value as $val) {
                                    $line .= "<" . XmpParser::RDF_LI ."> ";
                                    $line .= $val;
                                    $line .= "</" .XmpParser::RDF_LI . ">";
                                    $arrtEx_ = true;
                                }
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
                    }
                    else {
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
        $data = $request['Description'];
        $nameData = array_keys($data);
        $attributesName = array_keys($request['Description']['@attributes']);
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
        foreach ($attributesName  as $value) {
        foreach (array_keys($request['Description']['@attributes'][$value]) as $valueN) {
            $name=$value.':'.$valueN;
            foreach ($tegs as $valueT) {
                if (strcmp($name, $valueT) == 0) {
                    $dataAttributes = ['XmpText' => $request['Description']['@attributes'][$value][$valueN]];
                    $same[] = ['name' => $valueT, 'data' => $dataAttributes];
                }
            }
         }
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

                        foreach ($attributesName as $val) {
                            if (strcmp($valui["prefix"],$val) == 0 && strcmp($valui["prefix"],'Iptc4xmpCore') != 0) {
                                if (isset($vln['data']['XmpText'])) {
                                    $text[] = $vln['data']['XmpText'];
                                }
                                if (isset($vln['data']['rdf:Bag'])) {
                                    $text = $vln['data']['rdf:Bag'];
                                }
                                if (isset($vln['data']['rdf:Alt'])) {
                                    $text[] = $vln['data']['rdf:Alt']['@content'];
                                }
                                $valui['text'] = $text;
                                $text = [];
                            }
                        }

                        if(strcmp($valui["prefix"], "Iptc4xmpCore") == 0)
                        {
                            if (isset($vln['data']['XmpText'])) {
                                $text[] = $vln['data']['XmpText'];
                            }
                            else { $text[] = $vln['data']; }
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

                        }
                        $result[] = $valui;
                    }
                }
            }
        }
        $i = 0; $l =0;
        $new = array_keys($request['Description']);
        foreach(array_keys($request['Description']) as $iptc) {
            if ($iptc == 'Iptc4xmpCore:CreatorContactInfo') {
                foreach(array_keys($request['Description'][$iptc]['@attributes']['Iptc4xmpCore']) as $iptcAttr) {
                    $pref = 'Iptc4xmpCore'.':'.$iptcAttr;
                    array_push($new, $pref);
                }
            }
        }
        foreach (array_keys($request['Description']['@attributes']) as $attr) {
            foreach (array_keys($request['Description']['@attributes'][$attr]) as $val) {
                if ($val == "CreatorContactInfo") {
                    foreach(array_keys($request['Description']['@attributes'][$attr][$val]['@attributes']['Iptc4xmpCore']) as $iptc) {
                        $pref = $attr . ':' . $iptc;
                        array_push($new, $pref);
                    }
                }
                $pref = $attr . ':' . $val;
                array_push($new, $pref);
            }
        }
        $lastList = $request['list'];
        $newList = $request['list'];
        foreach ($lastList as $value) {
            foreach ($new as $val) {
                $pref = stristr($val, ':', true);
                if($value['prefix'] == 'Iptc4xmpCore') {
                    foreach ($value['list'] as $listI) {
                        $without =substr(strrchr($val, ":"), 1);
                        if($listI['name'] == $without && $listI['name'] != 'CreatorContactInfo') {
                            $a[] =($newList[$i]['list'][$l]);
                        }
                        ++$l;
                    }$l = 0;
                }
                if($value['prefix'] == $pref) {
                    foreach ($value['list'] as $list) {
                        $without =substr(strrchr($val, ":"), 1);
                        if($list['name'] == $without) {
                            unset($newList[$i]["list"][$l]);
                        }
                        ++$l;
                    }$l = 0;
                }
            }
            ++$i;
        }
        $masL = [];
        foreach (array_keys($newList) as $list) {
            foreach ($newList[$list]['list'] as $listS) {
                array_push($masL,$listS);
            }
            $newList[$list]['list'] = [];
            array_push($newList[$list]['list'],$masL);
            $masL = [];
        }

        return response()->json( ['list2' => $result , 'list' => $newList ] );
    }

    public function changeXMP (Request $request)
    {
        $itemName = $request['item'];
        $changeXMP = $request['XMPforChange']['x:xmpmeta']['rdf:Description'];
        $xmpName = array_keys($changeXMP);  $core = []; $coreEnd = [];
        $attributesName = array_keys($request['XMPforChange']['x:xmpmeta']['rdf:Description']["@attributes"]);
        $attributesData = $request['XMPforChange']['x:xmpmeta']['rdf:Description']["@attributes"];
        foreach ($xmpName as $value) {
            if (strcmp($value, "Iptc4xmpCore:CreatorContactInfo") == 0) {
                $core = array_keys($changeXMP[$value]["@attributes"]["Iptc4xmpCore"]);
            }
        }
        foreach ($itemName as $value) {
            $newName[] = $value['item']["prefix"] . ":" . $value['item']['name'];
        }
        foreach ($core as $diff) {
            $coreEnd[] ="Iptc4xmpCore:".$diff;
        }
        foreach ($attributesName as $attr) {
            foreach (array_keys($attributesData[$attr]) as $attrN) {
                $coreEnd[] = $attr . ":" . $attrN;
                $diffAttributes[] = ['name' =>$attrN, 'prefix' => $attr];
            }
        }
        $newDiff = array_merge($xmpName,$coreEnd);
        $different = array_diff($newName, $newDiff);
        $number = array_keys($different);
        if(!empty($number)) {
            foreach ($number as $num) {
                $type = $itemName[$num]['item']['type'];
                if ($type == 'LangAlt') {
                    $text = ["@content" => $itemName[$num]["newText"][0]["text"], "@attributes" => ["xml" => ["lang" => "x-default"]]];
                    $contentTag = ["rdf:Alt" => $text];
                }
                if ($type == 'XmpBag') {
                    foreach ($itemName[$num]["newText"] as $bag) {
                        $bags[] = $bag["text"];
                    }
                    $text = $bags;
                    $contentTag = ["rdf:Bag" => $text];
                    $bags = [];
                }
                if ($type == 'XmpText') {
                    $text = $itemName[$num]["newText"][0]["text"];
                    $contentTag = $text;
                }
                if ($type == 'XmpSeq') {
                    foreach ($itemName[$num]["newText"] as $bag) {
                        $bags[] = $bag["text"];
                    }
                    $text = $bags;
                    $contentTag = ["rdf:Seq" => $text];
                    $bags = [];
                }
                $newItemTag[] = ["$different[$num]" => $contentTag];
            }
        }
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
            if (isset($diffAttributes)) {
                foreach ($diffAttributes as $core) {
                    foreach ($itemName as $item){
                        if(strcmp($core['name'],$item['item']['name']) == 0) {
                            $changeXMP["@attributes"][$core['prefix']][$core['name']]=$item["newText"][0]["text"];
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
        $count = count($newXMP['x:xmpmeta']['rdf:Description']);
        if(!empty($newItemTag))
        {
            $whithoutAttributes =  array_slice($newXMP['x:xmpmeta']['rdf:Description'], 0, $count-1);
            $attributes = ["@attributes" => end($newXMP['x:xmpmeta']['rdf:Description'])];

            foreach ($newItemTag as $val) {
                $tag = array_keys($val);
                $tagN = stristr($tag[0], ':', true);
                    if(isset($attributes["@attributes"][$tagN])) {
                        $name = substr(strrchr($tag[0], ":"), 1);
                        $label = [ $name => $val[$tag[0]]];
                        $attributesXMP = array_merge($attributes["@attributes"]['xmp'],$label);
                        $attributes["@attributes"]['xmp'] = $attributesXMP;
                    }
                    else{
                        $whithoutAttributes = array_merge($whithoutAttributes,$val);
                    }
            }
            $mergeDescription = array_merge($whithoutAttributes,$attributes);
            $newXMP['x:xmpmeta']['rdf:Description'] = $mergeDescription;
            return response()->json( ['newXMP' => $newXMP ]);

        }
        else {

            return response()->json( ['newXMP' => $newXMP ]);
        }
    }
}
