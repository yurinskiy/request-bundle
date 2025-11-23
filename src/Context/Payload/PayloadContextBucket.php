<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Payload;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\Context\Marked\MarkedContextBucket;

final class PayloadContextBucket extends MarkedContextBucket
{
    public function __construct(ContextInterface ...$contexts)
    {
        parent::__construct([PayloadContextInterface::class], ...$contexts);
    }

    public static function instance(ContextInterface ...$contexts): self
    {
        return new self(...$contexts);
    }
}
