<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Model;

use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;

final class ProcessedData
{
    private DataTypeEnum $type;

    /**
     * @var array|object|\stdClass
     */
    private $data = [];

    /**
     * @param object|array|\stdClass $data
     */
    public function __construct(DataTypeEnum $type, $data = [])
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * @return array|object|\stdClass
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
