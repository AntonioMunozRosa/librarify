<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Form\Model\BookDto;
use App\Form\Type\BookFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BooksController extends AbstractFOSRestController{

    public function __construct(
        private BookRepository $bookRepository,
        private EntityManagerInterface $entityManager,
        private FilesystemOperator $defaultStorage
    )
    {
    }

    /**
     * @Rest\Get(path="/books") Request
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */
    public function getAction( Request $request){
        return $this->bookRepository->findAll();
    }

    /**
     * @Rest\Post(path="/books") Request
     * @Rest\View(serializerGroups={"book"}, serializerEnableMaxDepthChecks=true)
     */
    public function postAction( Request $request){
        $bookDto = new BookDto();
        $form = $this->createForm(BookFormType::class, $bookDto);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $extension = explode('/', mime_content_type($bookDto->base64Image))[1];
            $data = explode(',', $bookDto->base64Image);
            $filename = sprintf('%s.%s', uniqid('book_', true), $extension);
            $this->defaultStorage->write($filename, base64_decode($data[1]));
            $book = new Book();
            $book->setTitle($bookDto->title);
            $book->setImage($filename);
            $this->entityManager->persist($book);
            $this->entityManager->flush();
            return $book;
        }

        return $form;


    }
}