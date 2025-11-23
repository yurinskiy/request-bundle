<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Handler;

use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

interface RequestHandlerInterface
{
    public function support(RequestProcessorModel $model): bool;

    public function execute(RequestProcessorModel $model): void;
}
