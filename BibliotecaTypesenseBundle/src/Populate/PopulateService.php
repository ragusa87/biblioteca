<?php

namespace Biblioteca\TypesenseBundle\Populate;

use Biblioteca\TypesenseBundle\Client\ClientInterface;
use Biblioteca\TypesenseBundle\Mapper\FieldMappingInterface;
use Biblioteca\TypesenseBundle\Mapper\MapperInterface;
use Biblioteca\TypesenseBundle\Mapper\MappingInterface;
use Http\Client\Exception;
use Typesense\Collection;
use Typesense\Exceptions\TypesenseClientError;

class PopulateService
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly int $batchSize = 100,
        private readonly string $collectionPrefix = '',
    ) {
    }

    public function deleteCollection(MapperInterface $mapper): void
    {
        $list = $this->client->getCollections()->retrieve();
        $names = array_map(fn ($collection) => $collection['name'], $list);
        $name = $this->getMappingName($mapper->getMapping());
        if (in_array($name, $names)) {
            $this->client->getCollections()->__get($name)->delete();
        }
    }

    /**
     * @throws Exception
     * @throws TypesenseClientError
     */
    public function createCollection(MapperInterface $mapper): Collection
    {
        $mapping = $mapper->getMapping();
        $name = $this->getMappingName($mapping);

        $payload = array_filter([
            'name' => $name,
            'fields' => array_map(fn (FieldMappingInterface $mapping) => $mapping->toArray(), $mapping->getFields()),
            'metadata' => $mapping->getMetadata() ? $mapping->getMetadata()?->toArray() : null,
            ...$mapping->getCollectionOptions()?->toArray() ?? [],
        ], fn ($value) => !is_null($value));

        $this->client->getCollections()->create($payload);

        return $this->client->getCollections()->__get($name);
    }

    public function fillCollection(MapperInterface $mapper): \Generator
    {
        $mapping = $mapper->getMapping();
        $name = $this->getMappingName($mapping);

        $collection = $this->client->getCollections()->offsetGet($name);
        $data = $mapper->getData();
        foreach (new BatchGenerator($data, $this->batchSize) as $items) {
            $collection->documents->import($items);
            yield from $items;
        }
    }

    private function getMappingName(MappingInterface $mapping): string
    {
        return $this->collectionPrefix.$mapping->getName();
    }
}
