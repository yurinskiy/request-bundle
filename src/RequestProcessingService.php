<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Yurinskiy\RequestBundle\Context\Filter\CodeContext;
use Yurinskiy\RequestBundle\Context\Meta\MetaContextBucket;
use Yurinskiy\RequestBundle\Context\Option\OptionContextBucket;
use Yurinskiy\RequestBundle\Context\Payload\PayloadContextBucket;
use Yurinskiy\RequestBundle\Context\UuidContext;
use Yurinskiy\RequestBundle\Event\AfterExecuteEvent;
use Yurinskiy\RequestBundle\Event\BeforeExecuteEvent;
use Yurinskiy\RequestBundle\Event\CompleteEvent;
use Yurinskiy\RequestBundle\Event\RequestEventInterface;
use Yurinskiy\RequestBundle\Exception\AddPayloadException;
use Yurinskiy\RequestBundle\Exception\CreatRequestProcessorModelException;
use Yurinskiy\RequestBundle\Exception\NotFoundRequestHandlerException;
use Yurinskiy\RequestBundle\Handler\DataResponseConverterInterface;
use Yurinskiy\RequestBundle\Handler\HandlerWithCodeInterface;
use Yurinskiy\RequestBundle\Model\PayloadData;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;
use Yurinskiy\RequestBundle\Resolver\RequestHandlerResolverInterface;
use Yurinskiy\RequestBundle\Storage\RequestProcessorStorageInterface;

class RequestProcessingService
{
    private RequestHandlerResolverInterface $handlerResolver;
    private RequestProcessorStorageInterface $requestStorage;
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(RequestHandlerResolverInterface $handlerResolver, RequestProcessorStorageInterface $requestStorage, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->handlerResolver = $handlerResolver;
        $this->requestStorage = $requestStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function execute(PayloadMessageInterface $message): RequestProcessorModel
    {
        $model = $this->requestStorage->find($message->getContext());
        if (!$model) {
            $model = $this->buildModel($message);
        }

        if ($model->getStatus()->isComplete()) {
            return $model;
        }

        try {
            $this->addPayload($model, $message);

            $this->executeByModel($model);
        } finally {
            $this->requestStorage->save($model);

            if ($model->getStatus()->isComplete()) {
                $this->dispatchEvent(new CompleteEvent($model));
            }

            return $model;
        }
    }

    /**
     * @throws CreatRequestProcessorModelException
     */
    protected function buildModel(PayloadMessageInterface $message): ?RequestProcessorModel
    {
        $context = $message->getContext();
        $codeContext = $context->last(CodeContext::class);
        $uuidContext = $context->last(UuidContext::class);

        try {
            $options = OptionContextBucket::instance(...$message->getContext()->toFlatArray());
            $meta = MetaContextBucket::instance(...$message->getContext()->toFlatArray());

            $model = new RequestProcessorModel(
                $uuidContext instanceof UuidContext ? $uuidContext->getUuid() : Uuid::v4()->toRfc4122(),
                null,
                $codeContext instanceof CodeContext ? $codeContext->getCode() : null,
                [],
                [],
                $options,
                $meta
            );
        } catch (\Throwable $exception) {
            $errorMessage = sprintf(
                'RequestProcessingService::createModel: Ошибка создания RequestProcessorModel с кодом %s и Uuid %s, %s ',
                $codeContext instanceof CodeContext ? $codeContext->getCode() : '!код не задан!',
                $uuidContext instanceof UuidContext ? $uuidContext->getUuid() : 'Uuid не задан',
                $exception->getMessage()
            );
            $this->failed($exception, $errorMessage);

            throw new CreatRequestProcessorModelException($errorMessage, $exception->getCode(), $exception);
        }

        return $model;
    }

    /**
     * @throws AddPayloadException
     */
    protected function addPayload(RequestProcessorModel $model, PayloadMessageInterface $message): void
    {
        try {
            $payload = new PayloadData(
                $message->getPayload(),
                PayloadContextBucket::instance(
                    ...$message->getContext()->toFlatArray()
                )
            );
            $model->addPayload($payload);
        } catch (\Throwable $exception) {
            $errorMessage = sprintf('RequestProcessingService::addPayload: Ошибка обновления RequestProcessorModel с Uuid %s, %s ', $model->getUuid(), $exception->getMessage());
            $this->failed($exception, $errorMessage, $model);

            throw new AddPayloadException($errorMessage, $exception->getCode(), $exception);
        }
    }

    protected function executeByModel(RequestProcessorModel $model): void
    {
        try {
            $handler = $this->handlerResolver->getRequestHandlerByModel($model);

            if (!$model->getCode()) {
                $code = $handler instanceof HandlerWithCodeInterface ? $handler->getCode() : get_class($handler);
                $model->setCode($code);
            }

            $this->dispatchEvent(new BeforeExecuteEvent($model));

            $handler->execute($model);

            $this->dispatchEvent(new AfterExecuteEvent($model));
        } catch (NotFoundRequestHandlerException $exception) {
            $this->failed(
                $exception,
                sprintf('RequestProcessingService::executeByModel: Не найден обработчик для сообщения. Uuid сообщения: %s ', $model->getUuid()),
                $model,
            );
        } catch (\Throwable $exception) {
            $this->failed(
                $exception,
                sprintf('RequestProcessingService::executeByModel: Ошибка выполнения обработчика с кодом %s, %s', $model->getCode(), $exception->getMessage()),
                $model,
            );
        }
    }

    protected function failed(\Throwable $exception, string $message, ?RequestProcessorModel $model = null): void
    {
        $this->logger->error($message, [
            'exception' => $exception,
            'model' => $model ?? 'не получено',
        ]);

        $model->setStatusFailed()->addDataError([$message]);
    }

    protected function dispatchEvent(RequestEventInterface $event): void
    {
        try {
            $this->eventDispatcher->dispatch($event);
        } catch (\Throwable $exception) {
            $model = $event->getModel();
            $this->logger->error(sprintf('RequestProcessingService::dispatchEvent: Отправка события %s завершилось ошибкой для кода %s c UUID %s', get_class($event), $model->getCode(), $model->getUuid()), [
                'exception' => $exception,
                'request' => $model,
            ]);
        } finally {
            return;
        }
    }

    /**
     * @throws NotFoundRequestHandlerException
     */
    protected function getResponseModel(RequestProcessorModel $model): ?object
    {
        $handler = $this->handlerResolver->getRequestHandlerByModel($model);
        if ($handler instanceof DataResponseConverterInterface) {
            return $handler->buildResponseData($model);
        }

        return null;
    }
}
