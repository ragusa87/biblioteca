<?php

namespace Biblioteca\TypesenseBundle\Populate;

use Biblioteca\TypesenseBundle\Client\ClientInterface;
use Biblioteca\TypesenseBundle\Mapper\FieldMappingInterface;
use Biblioteca\TypesenseBundle\Mapper\MapperInterface;
use Http\Client\Exception;
use Typesense\Collection;
use Typesense\Exceptions\TypesenseClientError;

class PopulateService
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly int $batchSize = 100,
    ) {
    }

    public function deleteCollection(string $name): void
    {
        $this->client->getCollections()->__get($name)->delete();
    }

    /**
     * @throws Exception
     * @throws TypesenseClientError
     */
    public function createCollection(string $name, MapperInterface $mapper): Collection
    {
        $mapping = $mapper->getMapping();

        $payload = array_filter([
            'name' => $name,
            'fields' => array_map(fn (FieldMappingInterface $mapping) => $mapping->toArray(), $mapping->getFields()),
            'metadata' => $mapping->getMetadata() ? $mapping->getMetadata()?->toArray() : null,
            ...$mapping->getCollectionOptions()?->toArray() ?? [],
        ], fn ($value) => !is_null($value));

        $this->client->getCollections()->create($payload);

        return $this->client->getCollections()->__get($name);
    }

    public function fillCollection(string $name, MapperInterface $mapper): \Generator
    {
        $collection = $this->client->getCollections()->offsetGet($name);
        $data = $mapper->getData();
        foreach (new BatchGenerator($data, $this->batchSize) as $items) {
            $collection->documents->import($items);
            yield from $items;
        }
    }
}
