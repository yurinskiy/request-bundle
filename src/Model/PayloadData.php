<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Model;

use Yurinskiy\RequestBundle\Context\Payload\PayloadContextBucket;

final class PayloadData
{
    /**
     * @var array|\stdClass|object
     */
    private $payload;
    private ?PayloadContextBucket $options = null;

    /**
     * @param array|\stdClass|object $payload
     */
    public function __construct($payload = [], ?PayloadContextBucket $options = null)
    {
        $this->payload = $payload;
        $this->options = $options ?? PayloadContextBucket::instance();
    }

    /**
     * @return array|\stdClass|object
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
