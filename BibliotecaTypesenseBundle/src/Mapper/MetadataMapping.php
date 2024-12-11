<?php

namespace Biblioteca\TypesenseBundle\Mapper;

class MetadataMapping implements MetadataMappingInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    public function __construct(private array $data)
    {
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
