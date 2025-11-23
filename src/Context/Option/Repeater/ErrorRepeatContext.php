<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Option\Repeater;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\RequestBundle\Context\Option\OptionContextInterface;

final class ErrorRepeatContext implements ContextInterface, OptionContextInterface
{
    private ?int $errorRepeatMs;

    public function __construct(?int $errorRepeatMs = null)
    {
        $this->errorRepeatMs = $errorRepeatMs;
    }

    public function getErrorRepeatMs(): ?int
    {
        return $this->errorRepeatMs;
    }
}
