<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Fixtures\Context;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

final class ModelContext implements ContextInterface
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
