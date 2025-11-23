<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Handler;

use Yurinskiy\Context\ContextBucket;
use Yurinskiy\RequestBundle\Context\Filter\CodeContext;
use Yurinskiy\RequestBundle\Context\Payload\PayloadContextBucket;
use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;
use Yurinskiy\RequestBundle\Model\PayloadData;
use Yurinskiy\RequestBundle\PayloadMessage;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\SimpleRequest\SimpleRequestHandler;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\SimpleRequest\SomeContext;

class SimpleRequestTest extends AbstractTestCase
{
    public function testSimpleRequest(): void
    {
        $message = new PayloadMessage(
            ['requestData' => 'test'],
            ContextBucket::instance(new CodeContext(SimpleRequestHandler::getCode()))
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusFailed());
        self::assertCount(1,  $model->getProcessedData());
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::ERROR()));
        self::assertArrayHasKey('error', $model->getLastProcessedData()->getData());
        $message = new PayloadMessage(
            ['requestData' => 'test'],
            ContextBucket::instance(
                new CodeContext(SimpleRequestHandler::getCode()),
                new SomeContext('OK')
            )
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isStatusSuccess());
        self::assertCount(2,  $model->getProcessedData());
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::RESPONSE()));
        self::assertArrayHasKey('response', $model->getLastProcessedData(DataTypeEnum::RESPONSE())->getData());

        $message = new PayloadMessage(
            ['requestData' => 'test'],
            ContextBucket::instance(new SomeContext('OK'))
        );
        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isStatusSuccess());
        self::assertCount(2,  $model->getProcessedData());
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::REQUEST()));
        self::assertArrayHasKey('request', $model->getLastProcessedData(DataTypeEnum::REQUEST())->getData());
    }

}
