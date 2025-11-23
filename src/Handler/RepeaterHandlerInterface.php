<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Handler;

use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

interface RepeaterHandlerInterface
{
    public function isNeedSend(RequestProcessorModel $model): bool;

    public function send(RequestProcessorModel $model): void;
}
