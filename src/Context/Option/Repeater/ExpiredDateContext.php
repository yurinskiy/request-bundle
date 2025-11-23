<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Context\Option\Repeater;

use Yurinskiy\Context\ContextInterface;
use Yurinskiy\RequestBundle\Context\Option\OptionContextInterface;

final class ExpiredDateContext implements ContextInterface, OptionContextInterface
{
    private \DateTimeInterface $expiredDate;

    public function __construct(\DateTimeInterface $expiredDate)
    {
        $this->expiredDate = $expiredDate;
    }

    public function getExpiredDate(): \DateTimeInterface
    {
        return $this->expiredDate;
    }
}
