<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle;

use Yurinskiy\Context\ContextBucket;

class PayloadMessage implements PayloadMessageInterface
{
    /**
     * @var array|object
     */
    protected $payload = [];
    protected ?ContextBucket $contextBucket = null;

    /**
     * @param array|object $payload
     */
    public function __construct($payload = [], ?ContextBucket $contextBucket = null)
    {
        $this->payload = $payload;
        $this->contextBucket = $contextBucket ?? ContextBucket::instance();
    }

    /**
     * @return array|object
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function getContext(): ContextBucket
    {
        return $this->contextBucket;
    }
}
