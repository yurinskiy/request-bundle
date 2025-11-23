<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Yurinskiy\RequestBundle\Handler\RepeaterHandlerInterface;
use Yurinskiy\RequestBundle\Handler\RequestHandlerInterface;

class YurinskiyRequestExtension extends Extension
{
    public const TAG_REQUEST_HANDLER = 'yurinskiy.request_handler';

    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(RequestHandlerInterface::class)
            ->setTags([self::TAG_REQUEST_HANDLER]);
        $container->registerForAutoconfiguration(RepeaterHandlerInterface::class)
            ->setTags([self::TAG_REQUEST_HANDLER]);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');
    }
}
