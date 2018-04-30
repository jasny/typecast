<?php

namespace Jasny\TypeCast;

/**
 * Interface for type guess.
 */
interface TypeGuessInterface
{
    /**
     * Create a type guess object for these types
     *
     * @param array $types
     * @return static
     */
    public function forTypes(array $types): self;

    /**
     * Guess the handler for the value.
     *
     * @param mixed $value
     * @return string|null
     */
    public function guessFor($value): ?string;
}
