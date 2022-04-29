<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LibraryController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private BookRepository $bookRepository
    ){

    }

    /**
     * @Route("/books", name="books_get")
     */
    public function list(Request $request)
    {

        $title = $request->get('title','Berserk');
        $books = $this->bookRepository->findAll();
        $booksAsArray = [];

        foreach ($books as $book){
            $booksAsArray[]= [
                'id'=> $book->getId(),
                'title'=>$book->getTitle(),
                'image'=>$book->getImage()
            ];
        };

        $response = new JsonResponse();
        $response->setData([
            'success' => true,
            'data' => $booksAsArray]);
        return $response;
    }

    /**
     * @Route("/book/create", name="create_book")
     */
    public function createBook(Request $request){
        $book = new Book();
        $title = $request->get('title', null);
        $response = new JsonResponse();

        if(empty($title)){
            $response->setData([
                'success' => false,
                'error' => 'Title not valid',
                'data' => null]);
            return $response;
        }

        $book->setTitle($title);
        $this->entityManager->persist($book);
        $this->entityManager->flush();


        $response->setData([
            'success' => true,
            'data' => [
                [
                    'id' => $book->getId(),
                    'title' => $book->getTitle()
                ]
            ]]);
        return $response;

    }

}