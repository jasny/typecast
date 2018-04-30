<?php

namespace Jasny\TypeCast;

/**
 * Interface for an object that holds Handlers
 */
interface HandlerRepositoryInterface
{
    /**
     * Get the handler for a type
     *
     * @param string $type
     * @return HandlerInterface
     * @throws \OutOfBoundsException
     */
    public function getHandler(string $type): HandlerInterface;
}
