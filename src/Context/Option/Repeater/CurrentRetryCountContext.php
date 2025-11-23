<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Option\Repeater;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\RequestBundle\Context\Option\OptionContextInterface;

final class CurrentRetryCountContext implements ContextInterface, OptionContextInterface
{
    private int $count = 0;

    public function getCount(): int
    {
        return $this->count;
    }

    public function addRetry(): void
    {
        ++$this->count;
    }
}
