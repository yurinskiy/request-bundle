<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Filter;

use Yurinskiy\Context\ContextInterface;

final class CodeContext implements ContextInterface
{
    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
