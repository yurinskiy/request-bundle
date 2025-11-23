<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Resolver;

use Yurinskiy\RequestBundle\Exception\NotFoundRequestHandlerException;
use Yurinskiy\RequestBundle\Handler\HandlerWithCodeInterface;
use Yurinskiy\RequestBundle\Handler\RequestHandlerInterface;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;

class DefaultHandlerResolver implements RequestHandlerResolverInterface
{
    private iterable $withCodeHandlers = [];
    private iterable $handlers;

    public function __construct(iterable $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function getRequestHandlerByModel(RequestProcessorModel $model): RequestHandlerInterface
    {
        /** @var RequestHandlerInterface $handler */
        foreach ($this->getHandlers($model) as $handler) {
            if ($handler->support($model)) {
                return $handler;
            }
        }

        throw new NotFoundRequestHandlerException(sprintf('HandlerResolver::getRequestHandlerByModel завершился с ошибкой: не найден исполнитель для кода %s с идентификатором %s', $model->getCode() ?? 'не задан', $model->getUuid()));
    }

    private function getHandlers(RequestProcessorModel $model): iterable
    {
        $this->heatCache();

        return $this->withCodeHandlers[$model->getCode()] ?? $this->handlers;
    }

    private function heatCache(): void
    {
        if (!$this->withCodeHandlers) {
            foreach ($this->handlers as $handler) {
                if (!$handler instanceof RequestHandlerInterface) {
                    continue;
                }

                if ($handler instanceof HandlerWithCodeInterface) {
                    $this->withCodeHandlers[$handler::getCode()][] = $handler;
                }

                $this->handlers[] = $handler;
            }
        }
    }
}
