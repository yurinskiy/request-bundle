<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Handler;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Yurinskiy\RequestBundle\ConverterService;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;
use Yurinskiy\RequestBundle\Traits\ResponseTrait;

abstract class AbstractResponseHandler implements RequestHandlerInterface, HandlerWithCodeInterface
{
    use ResponseTrait;

    protected ConverterService $converter;
    protected ValidatorInterface $validator;

    public static function getCode(): string
    {
        return static::class;
    }

    public function __construct(ValidatorInterface $validator, ConverterService $converter)
    {
        $this->validator = $validator;
        $this->converter = $converter;
    }

    abstract protected function handleResponse(RequestProcessorModel $model, object $responseData): void;

    abstract protected function getResponseClass(): string;

    abstract protected function buildResponseData(RequestProcessorModel $model): object;

    public function support(RequestProcessorModel $model): bool
    {
        return $model->getCode() === static::getCode();
    }

    public function execute(RequestProcessorModel $model): void
    {
        $responseData = $this->buildResponseData($model);
        $errors = $this->validate($responseData);
        if ($errors->count()) {
            $this->statusErrorValidation($model, $errors);
        } else {
            $this->handleResponse($model, $responseData);
        }
    }

    public function validate(object $requestData): ConstraintViolationListInterface
    {
        return $this->validator->validate($requestData);
    }
}
