<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Option;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\Context\Marked\MarkedContextBucket;

final class OptionContextBucket extends MarkedContextBucket
{
    public function __construct(ContextInterface ...$contexts)
    {
        parent::__construct([OptionContextInterface::class], ...$contexts);
    }

    public static function instance(ContextInterface ...$contexts): self
    {
        return new self(...$contexts);
    }
}
