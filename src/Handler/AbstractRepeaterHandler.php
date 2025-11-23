<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Handler;

use Yurinskiy\RequestBundle\Traits\RepeaterTrait;

abstract class AbstractRepeaterHandler extends AbstractRequestHandler implements RepeaterHandlerInterface
{
    use RepeaterTrait;
}
