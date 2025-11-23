<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Yurinskiy\RequestBundle\DependencyInjection\YurinskiyRequestExtension;
use Yurinskiy\RequestBundle\Event\AfterExecuteEvent;
use Yurinskiy\RequestBundle\EventListeners\AfterExecuteListener;
use Yurinskiy\RequestBundle\PayloadMessage;
use Yurinskiy\RequestBundle\Queue\QueueHandler;
use Yurinskiy\RequestBundle\RequestProcessingService;
use Yurinskiy\RequestBundle\Resolver\DefaultHandlerResolver;
use Yurinskiy\RequestBundle\Resolver\RequestHandlerResolverInterface;
use Yurinskiy\RequestBundle\Storage\RequestProcessorStorageInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(AfterExecuteListener::class)
        ->arg(0, RequestHandlerResolverInterface::class)
        ->arg(1, LoggerInterface::class)
        ->tag('kernel.event_listener', ['event' => AfterExecuteEvent::class]);

    $services->set(QueueHandler::class)
        ->tag('messenger.message_handler', ['handle' => PayloadMessage::class]);

    $services->set(RequestHandlerResolverInterface::class, DefaultHandlerResolver::class)
        ->arg(0, tagged_iterator(YurinskiyRequestExtension::TAG_REQUEST_HANDLER))
        ->alias(DefaultHandlerResolver::class, RequestHandlerResolverInterface::class);

    $services->set(RequestProcessingService::class)
        ->arg(0, service(DefaultHandlerResolver::class))
        ->arg(1, service(RequestProcessorStorageInterface::class))
        ->arg(2, service('event_dispatcher'))
        ->arg(3, service(LoggerInterface::class));
};