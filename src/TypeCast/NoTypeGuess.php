<?php

namespace Jasny\TypeCast;

/**
 * Don't do any real type guessing, just check if the value has one of the types already.
 */
class NoTypeGuess implements TypeGuessInterface
{
    /**
     * Guess the handler for the value.
     *
     * @param mixed    $value
     * @param string[] $types
     * @return string|null
     */
    public function guess($value, array $types): ?string
    {
        return $this->checkType($value, $types) ?? $this->checkClass($value, $types);
    }

    /**
     * Check if value is one of the specified types
     *
     * @param mixed $value
     * @param array $types
     * @return string|null
     */
    protected function checkType($value, $types): ?string
    {
        $type = gettype($value);

        return in_array($type, $types) ? $type : null;
    }

    /**
     * Guess the handler for the value.
     *
     * @param mixed    $value
     * @param string[] $types
     * @return string|null
     */
    protected function checkClass($value, array $types): ?string
    {
        if (!is_object($value)) {
            return null;
        }

        foreach ($types as $type) {
            if (class_exists($type) && is_a($value, $type)) {
                return $type;
            }
        }

        return null;
    }
}
