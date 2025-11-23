<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Option\Repeater;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\RequestBundle\Context\Option\OptionContextInterface;

final class AmqpGroupContext implements ContextInterface, OptionContextInterface
{
    private ?string $group;

    public function __construct(?string $group = null)
    {
        $this->group = $group;
        $this->group ??= 'default';
    }

    public function getGroup(): string
    {
        return $this->group;
    }
}
