<?php

namespace Biblioteca\TypesenseBundle\Query;

interface SearchParameterInterface
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(): array;
}
