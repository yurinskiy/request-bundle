<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Traits;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

trait ResponseTrait
{
    protected function statusErrorValidation(RequestProcessorModel $model, ConstraintViolationListInterface $errors): self
    {
        $errorList = [];
        /** @var ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $errorList[] = [
                'propertyPath' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        $model->setStatusFailed()
            ->addDataValidation($errorList);

        return $this;
    }

    protected function statusError(RequestProcessorModel $model, array $result = []): self
    {
        $model->setStatusFailed()
            ->addDataError($result);

        return $this;
    }

    protected function statusSuccess(RequestProcessorModel $model, array $result = []): self
    {
        $model->setStatusSuccess()
            ->addDataResponse($result);

        return $this;
    }

    protected function statusWait(RequestProcessorModel $model, array $result = []): self
    {
        $model->setStatusWait();

        if ($result) {
            $model->addDataResponse($result);
        }

        return $this;
    }
}
