<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Handler;

use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

interface DataResponseConverterInterface
{
    public function buildResponseData(RequestProcessorModel $model): ?object;
}
