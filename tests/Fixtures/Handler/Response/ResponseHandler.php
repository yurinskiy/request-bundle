<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Fixtures\Handler\Response;

use Yurinskiy\RequestBundle\Handler\AbstractResponseHandler;
use Yurinskiy\RequestBundle\Model\PayloadData;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

class ResponseHandler extends AbstractResponseHandler
{
    public static function getCode(): string
    {
        return 'testResponse';
    }

    public function support(RequestProcessorModel $model): bool
    {
        $payload = $model->getLastPayload();
        if (!$payload instanceof PayloadData) {
            return false;
        }

        $array = $payload->getPayload();
        if (!is_array($array) || !array_key_exists('uuid', $array)) {
            return false;
        }
        return $array['uuid'] === $model->getUuid();
    }

    protected function getResponseClass(): string
    {
        return ResponseModel::class;
    }

    /**
     * * @param ResponseModel $responseData
     */
    protected function handleResponse(RequestProcessorModel $model, object $responseData): void
    {
        if ($responseData->data == 'OK') {
            $model->setStatusSuccess()
                ->addDataResponse(['response' => 'OK']);
        } else {
            $model->setStatusFailed()
                ->addDataError(['error' => 'Invalid data']);
        }
    }

    protected function buildResponseData(RequestProcessorModel $model): object
    {
        $response = new ResponseModel();

        $payload = $model->getLastPayload()->getPayload();
        if (isset($payload['data'])) {
            $response->data = (string) $payload['data'];
        }

        return $response;
    }
}
