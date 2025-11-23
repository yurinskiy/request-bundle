<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Queue;

use Yurinskiy\RequestBundle\PayloadMessage;
use Yurinskiy\RequestBundle\RequestProcessingService;

final class QueueHandler
{
    private RequestProcessingService $service;

    public function __construct(RequestProcessingService $service)
    {
        $this->service = $service;
    }

    public function __invoke(PayloadMessage $message)
    {
        $this->service->execute($message);
    }
}
