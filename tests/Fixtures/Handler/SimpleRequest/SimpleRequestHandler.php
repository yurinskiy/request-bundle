<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Fixtures\Handler\SimpleRequest;

use Yurinskiy\RequestBundle\Handler\HandlerWithCodeInterface;
use Yurinskiy\RequestBundle\Handler\RequestHandlerInterface;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;
use Yurinskiy\RequestBundle\Traits\ResponseTrait;

class SimpleRequestHandler implements RequestHandlerInterface, HandlerWithCodeInterface
{
    use ResponseTrait;

    public static function getCode(): string
    {
        return 'testRequest';
    }

    public function support(RequestProcessorModel $model): bool
    {
        /** @var SomeContext|null $someContext */
        $someContext = $model->getOptions()->last(SomeContext::class);

        return $model->getCode() === self::getCode() || $someContext;
    }

    public function execute(RequestProcessorModel $model): void
    {
        /** @var SomeContext|null $someContext */
        $someContext = $model->getOptions()->last(SomeContext::class);

        if ($someContext && 'OK' == $someContext->getData()) {
            $model->setStatusSuccess()
                ->addDataRequest(['request' => 'OK'])
                ->addDataResponse(['response' => 'OK']);
        } else {
            $model->setStatusFailed()
                ->addDataError(['error' => 'Invalid data']);
        }
    }
}
