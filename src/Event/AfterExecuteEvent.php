<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

class AfterExecuteEvent extends Event implements RequestEventInterface
{
    private RequestProcessorModel $model;

    public function __construct(RequestProcessorModel $model)
    {
        $this->model = $model;
    }

    public function getModel(): RequestProcessorModel
    {
        return $this->model;
    }
}
