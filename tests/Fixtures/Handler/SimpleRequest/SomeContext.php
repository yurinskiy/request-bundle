<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Fixtures\Handler\SimpleRequest;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\RequestBundle\Context\Option\OptionContextInterface;

final class SomeContext implements ContextInterface, OptionContextInterface
{
    private string $data = '';

    public function __construct(string $data = '')
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
