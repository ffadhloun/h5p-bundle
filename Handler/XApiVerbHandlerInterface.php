<?php

namespace Studit\H5PBundle\Handler;

interface XApiVerbHandlerInterface
{
    public function supports(string $verb): bool;
    public function handle(array $statement, mixed $data): void;
}