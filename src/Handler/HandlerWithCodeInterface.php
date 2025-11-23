<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Handler;

interface HandlerWithCodeInterface
{
    public static function getCode(): string;
}
