<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/authors')]

class AuthorController extends AbstractController
{
    #[Route('/', name: 'app_authors')]
    public function index(BookRepository $bookRepository): Response
    {
        $authors = $bookRepository->getAllAuthors();

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/{slug}/{page}', name: 'app_author_detail', requirements: ['page' => '\d+'])]
    public function detail(string $slug, BookRepository $bookRepository, PaginatorInterface $paginator, int $page=1): Response
    {
        $authors = $bookRepository->getAllAuthors();
        $author = array_filter($authors, fn($serie) => $serie['authorSlug'] === $slug);

        $author= current($author);


        $pagination = $paginator->paginate(
            $bookRepository->getByAuthorQuery($slug),
            $page,
            18
        );

        return $this->render('author/detail.html.twig', [
            'pagination' => $pagination,
            'author' =>$author
        ]);
    }
}
