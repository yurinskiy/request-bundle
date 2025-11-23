<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Option\Repeater;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\RequestBundle\Context\Option\OptionContextInterface;

final class WaitRepeatContext implements ContextInterface, OptionContextInterface
{
    private ?int $waitTimeMs;

    public function __construct(?int $waitTimeMs = null)
    {
        $this->waitTimeMs = $waitTimeMs;
    }

    public function getWaitTimeMs(): ?int
    {
        return $this->waitTimeMs;
    }
}
