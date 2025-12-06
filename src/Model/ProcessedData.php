<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Model;

use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;

final class ProcessedData
{
    private DataTypeEnum $type;

    /**
     * @var array|\stdClass|object
     */
    private $data = [];

    /**
     * @param array|\stdClass|object $data
     */
    public function __construct(DataTypeEnum $type, $data = [])
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * @return array|\stdClass|object
     */
    public function getData()
    {
        return $this->data;
    }

    public function getType(): DataTypeEnum
    {
        return $this->type;
    }
}
