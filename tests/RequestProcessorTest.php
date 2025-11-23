<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Yurinskiy\Context\ContextBucket;
use Yurinskiy\RequestBundle\Context\Filter\CodeContext;
use Yurinskiy\RequestBundle\Context\UuidContext;
use Yurinskiy\RequestBundle\ConverterService;
use Yurinskiy\RequestBundle\Exception\NotFoundRequestHandlerException;
use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;
use Yurinskiy\RequestBundle\PayloadMessage;
use Yurinskiy\RequestBundle\RequestProcessingService;
use Yurinskiy\RequestBundle\Resolver\RequestHandlerResolverInterface;
use Yurinskiy\RequestBundle\Storage\RequestProcessorStorageInterface;

class RequestProcessorTest extends KernelTestCase
{
    protected ?RequestProcessingService $service = null;

    protected ?ValidatorInterface $validator;
    protected ?ConverterService $converter;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var RequestHandlerResolverInterface|MockObject $resolver */
        $resolver = $this->createMock(RequestHandlerResolverInterface::class);
        $resolver->method('getRequestHandlerByModel')->willThrowException(new NotFoundRequestHandlerException());

        /** @var RequestProcessorStorageInterface|MockObject $storage */
        $storage = $this->createMock(RequestProcessorStorageInterface::class);
        $storage->method('find')->willReturn(null);

        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->service = new RequestProcessingService(
            $resolver,
            $storage,
            $eventDispatcher,
            $logger,
        );
    }

    public function testExecute(): void
    {
        $message = new PayloadMessage(
            ['requestData' => 'test'],
        );

        $model = $this->service->execute($message);

        self::assertIsObject($model->getLastPayload());
        self::assertTrue($model->getStatus()->isComplete());
        self::assertTrue($model->getStatus()->isStatusFailed());
        self::assertCount(1, $model->getProcessedData());
        self::assertCount(1, $model->getProcessedData(DataTypeEnum::ERROR()));
        self::assertStringContainsString('Не найден обработчик для сообщения', $model->getLastProcessedData()->getData()[0]);
        self::assertNull($model->getCode());
        self::assertIsObject($model->getLastPayload());
        self::assertArrayHasKey('requestData', $model->getLastPayload()->getPayload());

        $codeContext = new CodeContext('Murzik');
        $uuidContext = new UuidContext('Hloya');
        $message = new PayloadMessage(
            ['requestData' => 'test'],
            ContextBucket::instance($codeContext, $uuidContext)
        );

        $model = $this->service->execute($message);
        self::assertEquals($codeContext->getCode(), $model->getCode());
        self::assertEquals($uuidContext->getUuid(), $model->getUuid());
    }
}
