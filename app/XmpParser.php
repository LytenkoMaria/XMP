<?php

namespace App;

use DOMDocument;
// use JeroenDesloovere\XmpMetadataExtractor\Exception\FileNotFoundException;
use SplFileInfo;
use Illuminate\Database;

class XmpParser
{
    public const RDF_ALT = 'rdf:Alt';
    public const RDF_BAG = 'rdf:Bag';
    public const RDF_LI = 'rdf:li';
    public const RDF_SEQ = 'rdf:Seq';
    public const POSSIBLE_CONTAINERS = [
        self::RDF_ALT,
        self::RDF_BAG,
        self::RDF_SEQ,
    ];
    private function convertDomNode($node)
    {
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                return trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                return $this->convertXmlNode($node);
                break;
        }
    }
    private function convertXmlNode($node)
    {
        $output = [];
        for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
            $child = $node->childNodes->item($i);

            $v = $this->convertDomNode($child);

            if (isset($child->tagName)) {


                $t = $child->tagName;
                if (!isset($output[$t])) {
                    $output[$t] = array();
                }

                $output[$t][] = $v;
            } elseif ($v || $v === '0') {
                $output = (string)$v;
            }
        }
        // Has attributes but isn't an array
        if ($node->attributes->length && !is_array($output)) {
            // Change output into an array.
            $output = array('@content' => $output);
        }
        if (is_array($output)) {
            if ($node->attributes->length) {
                $a = array();
                foreach ($node->attributes as $attrName => $attrNode) {
                    $a[(string)$attrNode->prefix][$attrName] = (string)$attrNode->value;
                }
                $output['@attributes'] = $a;
            }
            foreach ($output as $t => $v) {
                // We are combining arrays for rdf:Bag, rdf:Alt, rdf:Seq
                if (in_array($t, self::POSSIBLE_CONTAINERS)) {
                    if (!array_key_exists(self::RDF_LI, $v[0])) {
                        break;
                    }
                    $element = [];
                    $element[$t] = $v[0][self::RDF_LI];
                    $output = $element;
                    //$output = $v[0][self::RDF_LI];

                } elseif (is_array($v) && count($v) == 1 && $t != '@attributes') {
                    $output[$t] = $v[0];
                }
            }
        }
        return $output;
    }
    public function extractFromContent(string $content): array
    {
        try {
            $doc = new DOMDocument();
            $xmpContent = $this->getXmpXmlString($content);
            $xmpContent = $this -> normolizeXMP($xmpContent);
            $this->getTags($xmpContent);
            $doc->loadXML($xmpContent);
            $root = $doc->documentElement;
            $output = $this->convertDomNode($root);
            $output['@root'] = $root->tagName;
            return $output;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function normolizeXMP(string $content): string
    {
        $content = str_replace("\n", "", $content);

        do {
            $content = str_replace("  ", " ", $content);
            $index = strpos($content, "  ");
        } while($index !== false);

        return $content;
    }

    public function getTags(string $content)
    {
        $tags = [];
        $index = 0;
        do {
            $findIndex = strpos($content, "xmlns:", $index);
            if ($findIndex !== false) {
                $index = $findIndex + 6;
                $indexEnd = strpos($content, "=", $findIndex);
                $tag = substr($content, $findIndex, ($indexEnd - $findIndex));
                $tags[] = str_replace("xmlns:", "", $tag);
            }
        } while($findIndex !== false);
        //dd ($tags); die;
    }

    public function extractFromFile(string $file): array
    {
        try {
            $file = new SplFileInfo($file);
            $contents = file_get_contents($file->getPathname());
        } catch (\Exception $e) {
            // throw new FileNotFoundException('The given File could not be found.');
        }
        return $this->extractFromContent($contents);
    }
    private function getXmpXmlString(string $content): string
    {
        $xmpDataStart = strpos($content, '<x:xmpmeta');
        $xmpDataEnd = strpos($content, '</x:xmpmeta>');
        $xmpLength = $xmpDataEnd - $xmpDataStart;
        return substr($content, $xmpDataStart, $xmpLength + 12);
    }
}
