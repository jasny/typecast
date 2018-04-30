<?php

namespace Jasny\TypeCast;

/**
 * Don't do any type guessing to improve performance
 */
class NoTypeGuess implements TypeGuessInterface
{
    /**
     * Create a type guess object for these types
     *
     * @param array $types
     * @return $this
     */
    public function forTypes(array $types): TypeGuessInterface
    {
        return $this;
    }

    /**
     * Guess the handler for the value.
     *
     * @param mixed $value
     * @return string|null
     */
    public function guessFor($value): ?string
    {
        return null;
    }
}
