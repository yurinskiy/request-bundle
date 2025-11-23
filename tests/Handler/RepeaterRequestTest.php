<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Handler;

use Yurinskiy\Context\ContextBucket;
use Yurinskiy\RequestBundle\Context\Filter\CodeContext;
use Yurinskiy\RequestBundle\Context\Option\Repeater\CurrentRetryCountContext;
use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;
use Yurinskiy\RequestBundle\PayloadMessage;
use Yurinskiy\RequestBundle\Tests\Fixtures\Context\ModelContext;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\Repeater\RepeaterHandler;

class RepeaterRequestTest extends AbstractTestCase
{
    public function testRepeaterRequest(): void
    {
        $message = new PayloadMessage(
            ['requestData' => 'test'],
            ContextBucket::instance(
                new CodeContext(RepeaterHandler::getCode())
            )
        );

        $model = $this->service->execute($message);
        self::assertFalse($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusWait());
        self::assertCount(1, $model->getProcessedData(DataTypeEnum::REQUEST()));
        self::assertArrayHasKey('retryCount', $model->getLastProcessedData(DataTypeEnum::REQUEST())->getData());

        $message = new PayloadMessage(
            ['requestData' => 'test'],
            ContextBucket::instance(
                new CodeContext(RepeaterHandler::getCode()),
                new ModelContext($model)
            )
        );

        $model = $this->service->execute($message);

        self::assertFalse($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusWait());

        $message = new PayloadMessage(
            ['requestData' => 'test'],
            ContextBucket::instance(
                new CodeContext(RepeaterHandler::getCode()),
                new ModelContext($model)
            )
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getOptions()->has(CurrentRetryCountContext::class));

        /** @var CurrentRetryCountContext $context */
        $context = $model->getOptions()->last(CurrentRetryCountContext::class);
        self::assertEquals(2, $context->getCount());
    }
}
