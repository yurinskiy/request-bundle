<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Tests\Fixtures\Handler\Response;

use Symfony\Component\Validator\Constraints as Assert;

class ResponseModel
{
    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    public ?string $data = null;

    public function __construct(
        ?string $data = null
    ) {
        $this->data = $data;
    }
}
