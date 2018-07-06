<?php

namespace Jasny\TypeCast\Handler;

use Jasny\TypeCast\Handler;
use Jasny\TypeCast\HandlerInterface;
use LogicException;

/**
 * Type cast to an integer or float
 */
class NumberHandler extends Handler
{
    /**
     * @var string
     */
    protected $type;

    /**
     * Get the type what the handler is casting to.
     *
     * @return string
     */
    protected function getType(): string
    {
        return $this->type;
    }

    /**
     * Use handler to cast to type.
     *
     * @param string $type
     * @return static
     * @throws LogicException if handler can't be used
     */
    public function forType(string $type): HandlerInterface
    {
        if (!in_array($type, ['integer', 'float', 'integer|float', 'float|integer'])) {
            throw new LogicException("Unable to use " . get_class($this) . " to cast to $type");
        }

        if ($this->type === $type || ((strstr($this->type, '|') && strstr($type, '|')))) {
            return $this;
        }

        $handler = clone $this;
        $handler->type = $type;

        return $handler;
    }

    /**
     * Cast value to a number
     *
     * @param mixed $value
     * @return int|float|mixed
     */
    public function cast($value)
    {
        $fn = 'cast' . ucfirst(gettype($value));
        return method_exists($this, $fn) ? $this->$fn($value) : $this->dontCast($value);
    }


    /**
     * Cast integer to a number
     *
     * @param $value
     * @return int|float
     */
    protected function castInteger($value)
    {
        return $this->type === 'float' ? (float)$value : $value;
    }

    /**
     * Cast float to a number
     *
     * @param $value
     * @return int|float
     */
    protected function castFloat($value)
    {
        return $this->type === 'integer' ? (int)$value : $value;
    }

    /**
     * Alias of castFloat
     *
     * @param $value
     * @return int|float
     */
    final protected function castDouble($value)
    {
        return $this->castFloat($value);
    }

    /**
     * Cast boolean to a number
     *
     * @param $value
     * @return int|float
     */
    protected function castBoolean($value)
    {
        return $this->type === 'float' ? (float)$value : (int)$value;
    }

    /**
     * Cast a string to a number
     * 
     * @param string $value
     * @return int|float|string
     */
    protected function castString(string $value)
    {
        $val = trim($value);
    
        if (!is_numeric($val) && $val !== '') {
            return $this->dontCast($value);
        }
        
        $type = strstr($this->type, '|') ? (strstr($val, '.') ? 'float' : 'integer') : $this->type;
        settype($val, $type);

        return $val;
    }
}
