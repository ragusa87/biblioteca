<?php

namespace Biblioteca\TypesenseBundle\Mapper;

use Biblioteca\TypesenseBundle\Utils\ArrayAccessTrait;

/**
 * @implements \ArrayAccess<string, mixed>
 */
class MetadataMapping implements MetadataMappingInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    use ArrayAccessTrait;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
