<?php

namespace Jasny\TypeCast;

/**
 * Interface for type guess.
 */
interface TypeGuessInterface
{
    /**
     * Guess the handler for the value.
     *
     * @param mixed    $value
     * @param string[] $types
     * @return string|null
     */
    public function guess($value, array $types): ?string;
}
