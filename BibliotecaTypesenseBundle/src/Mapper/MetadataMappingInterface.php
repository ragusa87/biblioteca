<?php

namespace Biblioteca\TypesenseBundle\Mapper;

interface MetadataMappingInterface
{
    /**
     * Metadata options and value
     * @return array<string,mixed>
     */
    public function toArray(): array;
}
