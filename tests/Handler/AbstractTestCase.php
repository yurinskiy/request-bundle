<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Yurinskiy\Context\ContextBucket;
use Yurinskiy\RequestBundle\ConverterService;
use Yurinskiy\RequestBundle\Event\AfterExecuteEvent;
use Yurinskiy\RequestBundle\EventListeners\AfterExecuteListener;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;
use Yurinskiy\RequestBundle\PayloadMessageInterface;
use Yurinskiy\RequestBundle\RequestProcessingService;
use Yurinskiy\RequestBundle\Resolver\DefaultHandlerResolver;
use Yurinskiy\RequestBundle\Storage\RequestProcessorStorageInterface;
use Yurinskiy\RequestBundle\Tests\Fixtures\Context\ModelContext;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\AbstractRequest\AbstractRequestHandler;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\Repeater\RepeaterHandler;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\Response\ResponseHandler;
use Yurinskiy\RequestBundle\Tests\Fixtures\Handler\SimpleRequest\SimpleRequestHandler;

abstract class AbstractTestCase extends KernelTestCase
{
    protected ?RequestProcessingService $service = null;

    protected ?ValidatorInterface $validator;
    protected ?ConverterService $converter;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->validator = $container->get(ValidatorInterface::class);
        /** @var SerializerInterface $serializer */
        $serializer = new Serializer([new ObjectNormalizer(), new GetSetMethodNormalizer()], [new JsonEncoder()]);
        $this->converter = new ConverterService($serializer);

        $abstractRequest = new AbstractRequestHandler($this->validator, $this->converter);

        /** @var MessageBusInterface|MockObject $messageBus */
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->method('dispatch')
            ->willReturnCallback(function (PayloadMessageInterface $message): ?Envelope {
                return new Envelope($message);
            });

        $repeater = new RepeaterHandler();
        $repeater->setMessageBus($messageBus);

        $request = new SimpleRequestHandler();

        $response = new ResponseHandler($this->validator, $this->converter);

        /** @var RequestProcessorStorageInterface|MockObject $storage */
        $storage = $this->createMock(RequestProcessorStorageInterface::class);
        $storage->method('find')
            ->willReturnCallback(function (ContextBucket $filter): ?RequestProcessorModel {
                $model = $filter->last(ModelContext::class);

                return $model ? $model->getModel() : null;
            });

        $resolver = new DefaultHandlerResolver([$abstractRequest, $repeater, $request, $response]);

        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(AfterExecuteEvent::class, new AfterExecuteListener($resolver, $logger));

        $this->service = new RequestProcessingService(
            $resolver,
            $storage,
            $eventDispatcher,
            $logger,
        );
    }
}
