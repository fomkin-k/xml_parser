<?php

namespace App\Controller;

use App\Repository\XMLElementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ParseXMLElements;
use App\Service\GetContent;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\XMLElement;
use App\Form\XMLElementType;

class ParserController extends AbstractController
{
    #[Route('/get_file', name: 'get_file')]
    public function get_file(Request $request, EntityManagerInterface $entityManager, ParseXMLElements $parseXMLElements)
    {

        // Получение файла
        $xml_file = $request->files->get('xml_file');
        if ($xml_file) {
            $xml = simplexml_load_file($xml_file);

            $loaded = "OK";
            //Запуск функции, добавление сущностей в БД
            $elements = $parseXMLElements->parseXMLElements($xml);

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
    public function get_file_content( XMLElementRepository $XMLElementRepository, FormFactoryInterface $formFactory): Response
    {
        //получаем все элементы, которые у нас есть, отсортированные по коду
        $elements = $XMLElementRepository->findAllSorted();
        $element = new XMLElement();
        //форма создания элементов
        $create_form = $formFactory->createNamed("create_form", XMLElementType::class, $element, [
            'action' => $this->generateUrl('create_element'),
        ]);
        //форма редактирвоания элементов
        $edit_form = $formFactory->createNamed("edit_form", XMLElementType::class, $element, [
            'action' => $this->generateUrl('edit_element'),
        ]);
        return $this->render('file_content.html.twig', [
            "elements" => $elements,
            "create_form" => $create_form->createView(),
            "edit_form" => $edit_form->createView()
        ]);
    }

    #[Route('/get_file_content/create_element', name: 'create_element')]
    public function create_element(Request $request, XMLElementRepository $XMLElementRepository, FormFactoryInterface $formFactory, EntityManagerInterface $entityManager, NotifierInterface $notifier): Response
    {
        //Получение кода элемента
        $code = $request->request->all()['create_form']['code'];
        //Если элемент с таким кодом уже существует, то его нельзя создать, форма сворачивается, выводится соответсвующее сообщение
        if ($XMLElementRepository->findOneByCode($code)) {
            $notifier->send(new Notification('Элемент с таким кодом уже существует.', ['browser']));
            return $this->redirectToRoute('get_file_content');
        }
        //Созадние нового элемента
        $new_element = new XMLElement();
        $new_element->setContent($request->request->all()['create_form']['content']);
        $new_element->setCode($code);
        preg_match('/.*(?=\.\d+$)/', $code, $matches); //получает код перед последней точкой, то есть код родителя
        //Добавление кода родителя
        if ($matches) {
            $new_element->setParentCode($matches[0]);
        }
        //Отрисовка формы, получение из неё введённых данных
        $create_form = $formFactory->createNamed("create_form", XMLElementType::class, $new_element);
        $create_form->handleRequest($request);
        if ($create_form->isSubmitted() && $create_form->isValid()) {
            $entityManager->persist($new_element);
            $entityManager->flush();
            return $this->redirectToRoute('get_file_content');
        }

        return $this->redirectToRoute('get_file_content');
    }

    #[Route('/get_file_content/edit_element', name: 'edit_element')]
    public function edit_element(Request $request, XMLElementRepository $XMLElementRepository, FormFactoryInterface $formFactory, EntityManagerInterface $entityManager, NotifierInterface $notifier)
    {
        //Получение кода элемента
        $code = $request->request->all()['edit_form']['code'];
        //Если такого элемента нет, то мы не можем его редактировать, форма сворачивается, выводится соответствующее сообщение
        if(!$XMLElementRepository->findOneByCode($code)){
            $notifier->send(new Notification('Такой элемент не существует.', ['browser']));
            return $this->redirectToRoute('get_file_content');
        }
        //Отрисовка формы, получение данных из неё
        $edit_element = $XMLElementRepository->findOneByCode($code);
        $edit_form = $formFactory->createNamed('edit_form', XMLElementType::class, $edit_element);
        $edit_form->handleRequest($request);
        if ($edit_form->isSubmitted() && $edit_form->isValid()) {
            $entityManager->persist($edit_element);
            $entityManager->flush();

            return $this->redirectToRoute('get_file_content');
        }
        return $this->redirectToRoute('get_file_content');
    }

    #[Route('/get_file_content/delete_element/{code}', name: 'delete_element')]
    public function delete_element(string $code, XMLElementRepository $XMLElementRepository, EntityManagerInterface $entityManager, GetContent $getContent): Response
    {
        //Удаление элемента и всех дочерних элементов из БД
        $elements = [];
        $childrenCodes = $getContent->getChildrenByCode($code);
        rsort($childrenCodes);
        foreach ($childrenCodes as $childCode) {
            $elements[] = $XMLElementRepository->findOneByCode($childCode);
        }
        foreach ($elements as $element) {
            $entityManager->remove($element);
            $entityManager->flush();
        }
        return $this->redirectToRoute('get_file_content');
    }

    #[Route('/get_file_content/make_xml', name: 'make_xml')]
    public function make_xml(XMLElementRepository $XMLElementRepository)
    {
        //Создание XML документа на основе элементов в БД
        $result = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $elements = $XMLElementRepository->findAll();
        $result .= "<book>";
        foreach ($elements as $element) {
            $result .= "\t<element>\n";
            $result .= "\t\t<code>" . $element->getCode() . "</code>\n";
            $result .= "\t\t<title>" . $element->getContent() . "</title>\n";
            $result .= "\t</element>\n";
        }
        $result .= "</book>";
        $file = "C:\Users\Кирилл Фомкин\OneDrive\Рабочий стол\\new_file.xml";
        file_put_contents($file, $result);
        return $this->redirectToRoute('get_file_content');
    }
}
