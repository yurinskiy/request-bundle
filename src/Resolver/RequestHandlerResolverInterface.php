<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Resolver;

use Yurinskiy\RequestBundle\Exception\NotFoundRequestHandlerException;
use Yurinskiy\RequestBundle\Handler\RequestHandlerInterface;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

interface RequestHandlerResolverInterface
{
    /**
     * @throws NotFoundRequestHandlerException
     */
    public function getRequestHandlerByModel(RequestProcessorModel $model): RequestHandlerInterface;
}
