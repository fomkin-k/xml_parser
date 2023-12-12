<?php

namespace App\Service;

use App\Entity\XMLElement;

class ParseXMLElements
{

    //Функция, создающая сущности элементов XML
    public function parseXMLElements(\SimpleXMLElement $xml)
    {
        $elements = [];
        foreach ($xml->children() as $child) {

            $element = new XMLElement();

            foreach ($child->children() as $tag) {
                //Добавление кода, текста, кода родителя
                if ($tag->getName() == "code") {
                    $str = (string) $tag;
                    preg_match('/.*(?=\.\d+$)/', $str, $matches);//получает код перед последней точкой
                    if($matches){
                        $element->setParentCode($matches[0]);
                    }
                    $element->setCode($str);
                } else if ($tag->getName() == "title") {
                    $element->setContent((string)$tag);
                }
            }

            $elements[] = $element;
        }
        return $elements;
    }
}
