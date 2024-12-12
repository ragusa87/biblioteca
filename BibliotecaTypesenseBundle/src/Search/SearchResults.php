<?php

namespace Biblioteca\TypesenseBundle\Search;

use Biblioteca\TypesenseBundle\Utils\ArrayAccessTrait;

/**
 * @implements \ArrayAccess<string, mixed>
 */
class SearchResults implements \ArrayAccess, \IteratorAggregate, \Countable
{
    use ArrayAccessTrait;
}
