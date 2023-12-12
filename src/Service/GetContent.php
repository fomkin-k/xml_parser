<?php

namespace App\Service;

use App\Repository\XMLElementRepository;
use  App\Entity\XMLElement;

class GetContent
{
    public function __construct(
        private XMLElementRepository $XMLElementRepository,
    ) {
    }
    //функция возвращает код элемента вместе с кодами дочерних элементов
    function getChildrenByCode($code){
        $codes=[];
        $codes[]=$code;
        $childElements= $this->XMLElementRepository->findElementsByParentCode($code);
        foreach($childElements as $childElement){
            $codes = array_merge($codes, $this->getChildrenByCode($childElement->getCode()));
        }
        return $codes;
    }
}
