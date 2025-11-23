<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Handler;

use Yurinskiy\Context\ContextBucket;
use Yurinskiy\RequestBundle\Context\Filter\CodeContext;
use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;
use Yurinskiy\RequestBundle\PayloadMessage;
use Yurinskiy\RequestBundle\Tests\Fixtures\Context\ModelContext;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\AbstractRequest\AbstractRequestHandler;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\AbstractRequest\RequestModel;

class AbstractRequestTest extends AbstractTestCase
{
    public function testResponseRequest(): void
    {
        $message = new PayloadMessage(
            ['uuid' => '1234567890'],
            ContextBucket::instance(new CodeContext(AbstractRequestHandler::getCode()))
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusFailed());

        $message = new PayloadMessage(
            new RequestModel(null),
            ContextBucket::instance(new CodeContext(AbstractRequestHandler::getCode()))
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusFailed());
        self::assertEquals(AbstractRequestHandler::getCode(), $model->getCode());

        self::assertCount(2,  $model->getProcessedData());
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::VALIDATION()));

        $message = new PayloadMessage(
            new RequestModel('Wrong data'),
            ContextBucket::instance(new CodeContext(AbstractRequestHandler::getCode()))
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusFailed());
        self::assertEquals(AbstractRequestHandler::getCode(), $model->getCode());
        self::assertCount(2,  $model->getProcessedData());
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::ERROR()));

        $message = new PayloadMessage(
            new RequestModel('REPEAT'),
            ContextBucket::instance(new CodeContext(AbstractRequestHandler::getCode()))
        );

        $model = $this->service->execute($message);

        self::assertFalse($model->getStatus()->isComplete());
        self::assertEquals(AbstractRequestHandler::getCode(), $model->getCode());
        self::assertCount(3,  $model->getProcessedData());
        self::assertCount(0,  $model->getProcessedData(DataTypeEnum::ERROR()));
        self::assertCount(2,  $model->getProcessedData(DataTypeEnum::REQUEST()));
        self::assertCount(1,  $model->getProcessedData(DataTypeEnum::RESPONSE()));
        self::assertCount(1,  $model->getPayloads());
        self::assertInstanceOf(RequestModel::class,  $model->getLastPayload()->getPayload());

        $message = new PayloadMessage(
            new RequestModel('SOME_DATA'),
            ContextBucket::instance(
                new CodeContext(AbstractRequestHandler::getCode()),
                new ModelContext($model)
            )
        );

        $model = $this->service->execute($message);

        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusSuccess());
        self::assertEquals(AbstractRequestHandler::getCode(), $model->getCode());
        self::assertCount(5,  $model->getProcessedData());
        self::assertCount(0,  $model->getProcessedData(DataTypeEnum::ERROR()));
        self::assertCount(3,  $model->getProcessedData(DataTypeEnum::REQUEST()));
        self::assertCount(2,  $model->getProcessedData(DataTypeEnum::RESPONSE()));
        self::assertCount(2,  $model->getPayloads());
        self::assertInstanceOf(RequestModel::class,  $model->getLastPayload()->getPayload());
        self::assertEquals('SOME_DATA',  $model->getLastPayload()->getPayload()->data);
    }
}
