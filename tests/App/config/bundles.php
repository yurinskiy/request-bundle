<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Yurinskiy\RequestBundle\YurinskiyRequestBundle;

return [
    FrameworkBundle::class => ['all' => true],
    YurinskiyRequestBundle::class => ['all' => true],
];