<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Fixtures\Handler\AbstractRequest;

use Yurinskiy\RequestBundle\Handler\AbstractRequestHandler as CommonAbstractRequest;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

class AbstractRequestHandler extends CommonAbstractRequest
{
    public static function getCode(): string
    {
        return 'testAbstractRequest';
    }

    public function support(RequestProcessorModel $model): bool
    {
        return $model->getCode() === self::getCode() && $model->getLastPayload()->getPayload() instanceof RequestModel;
    }

    protected function createRequestObject(RequestProcessorModel $model): object
    {
        return $model->getLastPayload()->getPayload();
    }

    protected function getRequestClass(): string
    {
        return RequestModel::class;
    }

    /**
     * * @param RequestModel $requestData
     */
    protected function handleRequest(RequestProcessorModel $model, object $requestData): void
    {
        if ('OK' == $requestData->data) {
            $model->setStatusSuccess()
                ->addDataRequest(['request' => 'OK'])
                ->addDataResponse(['response' => 'OK']);
        } elseif ('REPEAT' == $requestData->data) {
            $model->setStatusWait()
                ->addDataRequest(['data' => 'OK', 'description' => 'repeat check'])
                ->addDataResponse(['response' => 'REPEAT']);
        } else {
            $model->setStatusFailed()
                ->addDataError(['error' => 'Invalid data']);
        }
    }
}
