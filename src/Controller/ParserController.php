<?php

namespace App\Controller;

use App\Repository\XMLElementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use  App\Entity\XMLElement;

class ParserController extends AbstractController
{
    #[Route('/get_file', name: 'get_file')]
    public function get_file(Request $request, EntityManagerInterface $entityManager, XMLElementRepository $XMLElementRepository)
    {
        //Функция, создающая сущности элементов XML
        function parseXMLElements(\SimpleXMLElement $xml)
        {
            $elements = [];
            $elem_id = "";
            foreach ($xml->children() as $child) {
                //Добавление тэга, текста
                $element = new XMLElement();
                $element->setTag($child->getName());
                $element->setContent((string) $child);
                //добавление атрибутов
                $attributes = [];
                foreach ($child->attributes() as $key => $value) {
                    $attributes[$key] = (string) $value;
                }
                $element->setAttributes($attributes);
                //Добавление ID элемента
                $str = (string) $child;
                preg_match('/^[\d.]+$/', $str, $matches_1);
                if ($matches_1) {
                    $elem_id = $matches_1[0];
                    $element->setElementId($elem_id);
                } else {
                    $element->setElementId($elem_id);
                }
                //Добавление ID родителя
                preg_match('/.*(?=\.\d+$)/', $str, $matches_2);
                if ($matches_2) {
                    $parent_id = $matches_2[0];
                    $element->setParentId($parent_id);
                }
                //Рекурсивный запуск функции для детей
                if ($child->count() > 0) {
                    $children = parseXMLElements($child);
                    $elements = array_merge($elements, $children);
                }
                $elements[] = $element;
            }
            return $elements;
        }

        // Получение файла
        $xml_file = $request->files->get('xml_file');
        if ($xml_file) {
            $xml = simplexml_load_file($xml_file);

            $loaded = "OK";
            //Запуск функции, добавление сущностей в БД
            $elements = parseXMLElements($xml);

            foreach ($elements as $element) {
                $entityManager->persist($element);
            }

            $entityManager->flush();

            return $this->render('parser.html.twig', [
                "loaded" => $loaded,
            ]);
        }
        return $this->render('parser.html.twig', []);
    }

    #[Route('/get_file_content', name: 'get_file_content')]
    public function get_file_content(Request $request, EntityManagerInterface $entityManager, XMLElementRepository $XMLElementRepository): Response
    {
        //Функция, добавляющая текст в переменную для вывода
        function find_content(&$file_content, $id, $XMLElementRepository, $deep = 0)
        {
            //Поиск элементов по ID, добавление их в переменную
            $elements = $XMLElementRepository->findElementsById($id);
            if (!$elements) {
                return false;
            }
            foreach ($elements as $element) {
                $file_content .= $element->getContent() . " ";
            }
            //Поиск дочерних элементов, добавление их в переменнуб для вывода
            $kids = $XMLElementRepository->findElementsByParentId($id);
            if ($kids) {
                foreach ($kids as $kid) {
                    $deep++;
                    $file_content .= "\n";
                    for ($i = 0; $i < $deep; $i++) {
                        $file_content .= "\t";
                    }
                    find_content($file_content, $kid->getElementId(), $XMLElementRepository, $deep);
                    $deep--;
                }
            }
            return true;
        }

        $file_content = "";
        //Функция выполняется для всех элементов с element_ID в виде натурального числа
        $id = "1";
        while (true) {
            $param = find_content($file_content, $id, $XMLElementRepository);
            $file_content .= "\n";
            if ($param) {
                (int)$id++;
            } else {
                break;
            }
        }

        return $this->render('file_content.html.twig', [
            "file_content" => $file_content,
        ]);
    }
}


/* 

Пример данных для работы программы

Дано:

<?xml version="1.0" encoding="UTF-8" ?>
<book category="WEB">
    <element>
        <code>1</code>
        <title>Title</title>
    </element>
    <element>
        <code>1.1</code>
        <title>Title</title>
    </element>
    <element>
        <code>1.2</code>
        <title>Title</title>
    </element>
    <element>
        <code>1.2.1</code>
        <title>Title</title>
    </element>
    <element>
        <code>1.2.2</code>
        <title>Title</title>
    </element>
    <element>
        <code>1.3</code>
        <title>Title</title>
    </element>
    <element>
        <code>2</code>
        <title>Title</title>
    </element>
    <element>
        <code>3</code>
        <title>Title</title>
    </element>
    <element>
        <code>3.1</code>
        <title>Title</title>
    </element>
    <element>
        <code>3.2</code>
        <title>Title</title>
    </element>
    <element>
        <code>4</code>
        <title>Title</title>
    </element>
    <element>
        <code>4.1</code>
        <title>Title</title>
    </element>
    <element>
        <code>5</code>
        <title>Title</title>
    </element>
</book>

Результат:

1 Title
    1.1 Title
    1.2 Title
        1.2.1 Title
        1.2.2 Title
    1.3 Title
2 Title
3 Title
    3.1 Title
    3.2 Title
4 Title
    4.1 Title
5 Title

*/

//Регулярные выражения:
//\d+$ - находит последнее число в строке
//.*(?=\.\d+$) - находит числа перед последней точкой
//.*(?=\.\d+$)|^\d+$ - находит числа перед последей точкой или одиночные числа
//^[\d.]+$ - находит все номера глав( числа разделённые точкой)