<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Option\Repeater;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\RequestBundle\Context\Option\OptionContextInterface;

final class RetryCountContext implements ContextInterface, OptionContextInterface
{
    private int $retryCount;

    public function __construct(int $retryCount = 0)
    {
        $this->retryCount = $retryCount;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }
}
