<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Model;

use Yurinskiy\RequestBundle\Context\Payload\PayloadContextBucket;

final class PayloadData
{
    /**
     * @var array|object|\stdClass
     */
    private $payload;
    private ?PayloadContextBucket $options = null;

    /**
     * @param array|object|\stdClass $payload
     */
    public function __construct($payload = [], ?PayloadContextBucket $options = null)
    {
        $this->payload = $payload;
        $this->options = $options ?? PayloadContextBucket::instance();
    }

    /**
     * @return array|object|\stdClass
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function getOptions(): PayloadContextBucket
    {
        return $this->options;
    }
}
