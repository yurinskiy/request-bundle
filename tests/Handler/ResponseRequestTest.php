<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Handler;


use Yurinskiy\Context\ContextBucket;
use Yurinskiy\RequestBundle\Context\UuidContext;
use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;
use Yurinskiy\RequestBundle\PayloadMessage;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\Response\ResponseHandler;

class ResponseRequestTest extends AbstractTestCase
{
    public function testResponseRequest(): void
    {
        $message = new PayloadMessage(
            ['uuid' => '1234567890'],
            ContextBucket::instance(new UuidContext('1234567890'))
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusFailed());
        self::assertEquals(ResponseHandler::getCode(), $model->getCode());
        self::assertCount(1,  $model->getProcessedData());
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::VALIDATION()));

        $message = new PayloadMessage(
            ['data' => 'Cat\'s say Mew', 'uuid' => '1234567890'],
            ContextBucket::instance(new UuidContext('1234567890'))
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusFailed());
        self::assertCount(1,  $model->getProcessedData());
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::ERROR()));

        $message = new PayloadMessage(
            ['data' => 'OK', 'uuid' => '1234567890'],
            ContextBucket::instance(new UuidContext('1234567890'))
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusSuccess());
        self::assertCount(1,  $model->getProcessedData());
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::RESPONSE()));
    }
}
