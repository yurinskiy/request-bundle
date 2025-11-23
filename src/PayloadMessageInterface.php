<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle;

use Yurinskiy\Context\ContextBucket;

interface PayloadMessageInterface
{
    /**
     * @return array|object
     */
    public function getPayload();

    public function getContext(): ContextBucket;
}
