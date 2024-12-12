<?php

namespace Biblioteca\TypesenseBundle\CollectionName;

use Biblioteca\TypesenseBundle\Client\ClientInterface;
use Biblioteca\TypesenseBundle\Mapper\MappingInterface;

class AliasName implements NameInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $collectionTemplate = '%s',
    ) {
    }

    public function getName(string $name): string
    {
        $name = sprintf($this->collectionTemplate, $name);
        if (!$this->isAliasEnabled()) {
            return $name;
        }
        $date = (new \DateTimeImmutable())->format('Y-m-d-H-i-s');

        return sprintf('%s-%s', $name, $date);
    }

    public function isAliasEnabled(): bool
    {
        return true;
    }

    public function getPreviousName(string $name): ?string
    {
        if (!$this->isAliasEnabled()) {
            return null;
        }

        $aliases = $this->client->getAliases()->retrieve();
        $aliases = $aliases['aliases'] ?? [];
        $aliases = array_filter($aliases, fn ($alias) => $alias['name'] === $name);
        if ($aliases === []) {
            return null;
        }

        return $aliases[0]['collection_name'] ?? null;
    }

    public function switch(MappingInterface $mapping, string $source): void
    {
        if (!$this->isAliasEnabled()) {
            return;
        }

        // If alias was previously a collection, we delete it
        if ($this->client->getCollections()->__get($mapping->getName())->exists()) {
            $this->client->getCollections()->__get($mapping->getName())->delete();
        }

        // Point the alias to the new collection (the old collection is deleted automatically?)
        $this->client->getAliases()->upsert($mapping->getName(), ['collection_name' => $source]);
    }
}
