<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context;

use Yurinskiy\Context\ContextInterface;

final class UuidContext implements ContextInterface
{
    private string $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
