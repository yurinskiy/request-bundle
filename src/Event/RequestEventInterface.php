<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Event;

use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

interface RequestEventInterface
{
    public function getModel(): RequestProcessorModel;
}
