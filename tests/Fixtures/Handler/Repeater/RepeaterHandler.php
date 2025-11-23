<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Fixtures\Handler\Repeater;

use Carbon\CarbonImmutable;
use Yurinskiy\RequestBundle\Context\Option\Repeater\CurrentRetryCountContext;
use Yurinskiy\RequestBundle\Handler\RepeaterHandlerInterface;
use Yurinskiy\RequestBundle\Handler\RequestHandlerInterface;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;
use Yurinskiy\RequestBundle\Traits\RepeaterTrait;
use Yurinskiy\RequestBundle\Traits\ResponseTrait;

class RepeaterHandler implements RequestHandlerInterface, RepeaterHandlerInterface
{
    use ResponseTrait;
    use RepeaterTrait;

    public const REPEAT_COUNT = 2;

    protected function getRepeatTimeMs(): int
    {
        return 10;
    }

    protected function getErrorRepeatTimeMs(): int
    {
        return 20;
    }

    protected function getRetryCount(): int
    {
        return self::REPEAT_COUNT;
    }

    protected function getExpiredDate(): ?CarbonImmutable
    {
        return null;
    }

    public static function getCode(): string
    {
        return 'testRepeater';
    }

    public function support(RequestProcessorModel $model): bool
    {
        return $model->getCode() === self::getCode();
    }

    public function execute(RequestProcessorModel $model): void
    {
        $retryCountContext = $model->getOptions()->last(CurrentRetryCountContext::class);
        if (!$retryCountContext instanceof CurrentRetryCountContext) {
            $retryCountContext = new CurrentRetryCountContext();
            $model->getOptions()->add($retryCountContext);
        }

        if (self::REPEAT_COUNT === $retryCountContext->getCount()) {
            $model->setStatusSuccess()
                ->addDataResponse(['response' => 'OK']);

            return;
        }

        $model->setStatusWait()
            ->addDataRequest(['retryCount' => $retryCountContext->getCount() ?? 0]);
    }
}
