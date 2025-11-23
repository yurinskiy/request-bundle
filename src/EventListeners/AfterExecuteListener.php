<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\EventListeners;

use Psr\Log\LoggerInterface;
use Yurinskiy\RequestBundle\Event\AfterExecuteEvent;
use Yurinskiy\RequestBundle\Handler\RepeaterHandlerInterface;
use Yurinskiy\RequestBundle\Resolver\RequestHandlerResolverInterface;

class AfterExecuteListener
{
    private RequestHandlerResolverInterface $resolver;
    private LoggerInterface $logger;

    public function __construct(RequestHandlerResolverInterface $resolver, LoggerInterface $logger)
    {
        $this->resolver = $resolver;
        $this->logger = $logger;
    }

    public function __invoke(AfterExecuteEvent $event): void
    {
        $model = $event->getModel();
        $handler = $this->resolver->getRequestHandlerByModel($model);
        if (!$handler instanceof RepeaterHandlerInterface) {
            return;
        }

        try {
            if ($handler->isNeedSend($model)) {
                $handler->send($model);
            }
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('AfterExecuteListeners::__invoke: Обработка повторного запуска кода %s завершилось с ошибкой', $model->getCode()), [
                'exception' => $exception,
                'request' => $model,
            ]);
        }
    }
}
