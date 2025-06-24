<?php

namespace Studit\H5PBundle\Service;


use Studit\H5PBundle\Handler\XApiVerbHandlerInterface;

class XApiVerbHandlerRegistry
{
    /**
     * @param iterable<XApiVerbHandlerInterface> $handlers
     */
    public function __construct(private iterable $handlers) {}

    public function getHandler(string $verb): ?XApiVerbHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($verb)) {
                return $handler;
            }
        }

        return null;
    }
}