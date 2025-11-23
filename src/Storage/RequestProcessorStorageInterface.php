<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Storage;

use Yurinskiy\Context\ContextBucket;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

interface RequestProcessorStorageInterface
{
    public function find(ContextBucket $filter): ?RequestProcessorModel;

    public function save(RequestProcessorModel $model): void;
}
