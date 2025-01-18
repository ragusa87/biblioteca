<?php

namespace App\Service\Search;

use App\Ai\Communicator\AiAction;
use App\Ai\Communicator\AiCommunicatorInterface;
use App\Ai\Communicator\CommunicatorDefiner;
use App\Ai\Prompt\SearchHintPrompt;
use App\Entity\Book;
use Biblioteca\TypesenseBundle\Query\SearchQuery;
use Biblioteca\TypesenseBundle\Search\Results\SearchResultsHydrated;
use Biblioteca\TypesenseBundle\Search\SearchCollectionInterface;

class SearchHelper
{
    /** @var ?SearchResultsHydrated<Book> */
    public ?SearchResultsHydrated $response = null;
    public ?SearchQuery $query = null;
    public int $maxFacetValues = 10;

    /**
     * @param SearchCollectionInterface<Book> $searchBooks
     */
    public function __construct(protected SearchCollectionInterface $searchBooks, private readonly CommunicatorDefiner $communicatorDefiner)
    {
    }

    public function prepareQuery(string $q, ?string $filterBy = null, ?string $sortBy = null, int $perPage = 16, int $page = 1): SearchHelper
    {
        // TODO this->maxFacetValues;
        $this->query = new SearchQuery(
            q: $q,
            queryBy: 'title,serie,extension,authors,tags,summary',
            filterBy: $filterBy,
            sortBy: $sortBy,
            facetBy: 'authors,serie,tags',
            numTypos: 2,
            page: $page,
            perPage: $perPage,
            // TODO  addParameter('facet_strategy', 'exhaustive');
        );

        return $this;
    }

    public function execute(): SearchHelper
    {
        if (!$this->query instanceof SearchQuery) {
            return $this;
        }
        $this->response = $this->searchBooks->search($this->query);

        return $this;
    }

    /**
     * @return Book[]
     */
    public function getBooks(): array
    {
        if (null == $this->response) {
            return [];
        }

        return iterator_to_array($this->response->getIterator());
    }

    public function getFacets(): array
    {
        if (!$this->response instanceof SearchResultsHydrated) {
            return [];
        }

        return $this->response->getFacetCounts();
    }

    public function getPagination(): array
    {
        if (!$this->response instanceof SearchResultsHydrated) {
            return [
                'page' => 1,
                'total' => 0,
                'lastPage' => 1,
                'nextPage' => null,
                'previousPage' => null,
            ];
        }

        $found = $this->response->found();
        $perPage = $this->query?->toArray()['per_page'] ?? 1; // TODO read response['request_params']['per_page'] ?
        $lastPage = ceil($found / max($perPage, 1));

        return [
            'page' => $this->response->getPage(),
            'pages' => $this->response->getTotalPage(),
            'perPage' => $perPage,
            'total' => $this->response->found(),
            'lastPage' => $lastPage,
            'nextPage' => $this->response->getPage() < $lastPage ? ($this->response->getPage() ?? 1) + 1 : null,
            'previousPage' => $this->response->getPage() > 1 ? $this->response->getPage() - 1 : null,
        ];
    }

    public function getQueryHints(): ?array
    {
        $communicator = $this->communicatorDefiner->getCommunicator(AiAction::Search);
        if (!$communicator instanceof AiCommunicatorInterface || !$this->query instanceof SearchQuery) {
            return null;
        }

        $q = $this->query->toArray()['q']; // TODO Add query helper ?
        if ($q === '') {
            return null;
        }

        $facets = $this->getFacets();
        $facets = array_column($facets, 'counts', 'field_name');
        foreach ($facets as $key => $value) {
            array_walk($facets[$key], function (&$value) {$value = $value['value']; });
        }
        $prompt = new SearchHintPrompt();

        $communicator->getAiModel()->setSystemPrompt($prompt->getTypesenseNaturalLanguagePrompt($facets['serie'], $facets['authors'], $facets['tags']));

        $communicator->initialise($communicator->getAiModel());

        $prompt->setPrompt('### User-Supplied Query ###
'.$q);
        $result = $communicator->interrogate($prompt->getPrompt());

        return $prompt->convertResult($result);
    }
}
