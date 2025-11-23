<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Handler;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Yurinskiy\RequestBundle\ConverterService;
use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;
use Yurinskiy\RequestBundle\Traits\ResponseTrait;

abstract class AbstractRequestHandler implements RequestHandlerInterface, HandlerWithCodeInterface
{
    use ResponseTrait;

    protected ValidatorInterface $validator;
    protected ConverterService $converter;

    public static function getCode(): string
    {
        return static::class;
    }

    public function __construct(ValidatorInterface $validator, ConverterService $converter)
    {
        $this->converter = $converter;
        $this->validator = $validator;
    }

    abstract protected function createRequestObject(RequestProcessorModel $model): object;

    abstract protected function getRequestClass(): string;

    abstract protected function handleRequest(RequestProcessorModel $model, object $requestData): void;

    public function support(RequestProcessorModel $model): bool
    {
        return $model->getCode() === static::getCode();
    }

    public function execute(RequestProcessorModel $model): void
    {
        if ($model->getStatus()->isComplete()) {
            return;
        }

        $preparedData = $this->prepareData($model);

        $errors = $this->validate($preparedData);
        if ($errors->count()) {
            $this->statusErrorValidation($model, $errors);
        } else {
            $this->handleRequest($model, $preparedData);
        }
    }

    protected function prepareData(RequestProcessorModel $model): object
    {
        if ($model->getStatus()->isStatusNew()) {
            $data = $this->createRequestObject($model);

            $model->addDataRequest($this->converter->objectToArray($data));

            return $data;
        }

        return $this->getRequestObject($model);
    }

    protected function getRequestObject(RequestProcessorModel $model): object
    {
        return $this->converter->populate(
            $model->getLastProcessedData(DataTypeEnum::REQUEST())->getData(),
            $this->getRequestClass()
        );
    }

    protected function validate(object $requestData): ConstraintViolationListInterface
    {
        return $this->validator->validate($requestData);
    }
}
