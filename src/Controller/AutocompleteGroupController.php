<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @phpstan-type GroupType array{ item:string, slug:string, bookCount:int, booksFinished:int, lastBookIndex:int }
 */
class AutocompleteGroupController extends AbstractController
{
    #[Route('/autocomplete/group/{type}', name: 'app_autocomplete_group')]
    public function index(Request $request, BookRepository $bookRepository, string $type): Response
    {
        $query = $request->get('query');
        if (!is_string($query)) {
            return new JsonResponse(['results' => []]);
        }

        /** @var array<GroupType> $group */
        $group = match ($type) {
            'serie' => $bookRepository->getAllSeries()->getResult(),
            'authors' => $bookRepository->getAllAuthors(),
            'tags' => $bookRepository->getAllTags(),
            'publisher' => $bookRepository->getAllPublishers()->getResult(),
            default => [],
        };

        $exactmatch = false;
        $json = ['results' => []];

        if ($type !== 'authors' && $query === '' && $request->get('create', true) !== true) {
            $json['results'][] = ['value' => 'no_'.$type, 'text' => '[No '.$type.' defined]'];
        }
        foreach ($group as $item) {
            if (!str_contains(strtolower($item['item']), strtolower($query))) {
                continue;
            }
            if (strtolower($item['item']) === strtolower($query)) {
                $exactmatch = true;
            }
            $json['results'][] = ['value' => $item['item'], 'text' => $item['item']];
        }

        if (!$exactmatch && strlen($query) > 2 && $request->get('create', true) === true) {
            $json['results'][] = ['value' => $query, 'text' => 'New: '.$query];
        }

        return new JsonResponse($json);
    }
}
