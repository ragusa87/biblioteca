<?php

namespace Biblioteca\TypesenseBundle\Query;

class VectorQuery
{
    /**
     * @param array<float> $queryVector e.g., [0.1, 0.2, 0.3]
     */
    public function __construct(
        private readonly array $queryVector, // e.g., [0.1, 0.2, 0.3]
        private readonly int $numCandidates, // e.g., 100
        private readonly ?float $weight = null, // Optional weight
    ) {
    }
}
