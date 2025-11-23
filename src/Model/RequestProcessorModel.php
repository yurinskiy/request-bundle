<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Model;

use Yurinskiy\RequestBundle\Context\Meta\MetaContextBucket;
use Yurinskiy\RequestBundle\Context\Option\OptionContextBucket;
use Yurinskiy\RequestBundle\Model\Enum\DataTypeEnum;
use Yurinskiy\RequestBundle\Model\Enum\StatusEnum;

/**
 * Class RequestProcessorModel.
 *
 * @method self setStatusSuccess()
 * @method self setStatusFailed()
 * @method self setStatusWait()
 * @method self addDataError(array|object $data)
 * @method self addDataRequest(array|object $data)
 * @method self addDataResponse(array|object $data)
 * @method self addDataValidation(array|object $data)
 */
final class RequestProcessorModel
{
    private string $uuid;
    private StatusEnum $status;

    /** @var list<PayloadData> */
    private array $payload;

    /** @var list<ProcessedData> */
    private array $processedDataList;
    /** @var array<string, list<ProcessedData>> */
    private array $processedDataListByType = [];

    private ?string $code;
    private OptionContextBucket $options;
    private MetaContextBucket $meta;

    /**
     * @param list<ProcessedData> $processedDataList
     * @param list<PayloadData>   $payload
     */
    public function __construct(
        string $uuid,
        ?StatusEnum $status = null,
        ?string $code = null,
        array $payload = [],
        array $processedDataList = [],
        ?OptionContextBucket $options = null,
        ?MetaContextBucket $meta = null
    ) {
        $this->uuid = $uuid;
        $this->status = $status ?? StatusEnum::NEW();
        $this->code = $code;
        $this->payload = $payload;
        $this->options = $options ?? OptionContextBucket::instance();
        $this->meta = $meta ?? MetaContextBucket::instance();

        $this->processedDataList = [];
        foreach ($processedDataList as $processedDataItem) {
            $this->addToProcessedData($processedDataItem);
        }
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getOptions(): OptionContextBucket
    {
        return $this->options;
    }

    public function getMeta(): MetaContextBucket
    {
        return $this->meta;
    }

    public function getPayloads(): array
    {
        reset($this->payload);

        return $this->payload;
    }

    public function addPayload(PayloadData $payload): self
    {
        $this->payload[] = $payload;

        return $this;
    }

    public function getLastPayload(): ?PayloadData
    {
        return end($this->payload) ?: null;
    }

    /**
     * @return bool|self
     */
    public function __call($name, $arguments)
    {
        if (false !== stripos($name, 'setStatus') && strlen($name) > 9) {
            return $this->setStatus(lcfirst(substr($name, 9)));
        }

        if (false !== stripos($name, 'addData') && strlen($name) > 7) {
            return $this->addData(lcfirst(substr($name, 7)), $arguments[0]);
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method RequestProcessorModel::%s', $name));
    }

    public function getStatus(): StatusEnum
    {
        return $this->status;
    }

    private function setStatus(string $status): self
    {
        if ($status = StatusEnum::tryFrom($status)) {
            $this->status = $status;
        }

        return $this;
    }

    public function getProcessedData(?DataTypeEnum $filter = null): array
    {
        if ($filter) {
            return $this->processedDataListByType[$filter->value()] ?? [];
        }

        return $this->processedDataList;
    }

    public function getLastProcessedData(?DataTypeEnum $filter = null): ?ProcessedData
    {
        if ($filter) {
            return end($this->processedDataListByType[$filter->value()]) ?: null;
        }

        return end($this->processedDataList) ?: null;
    }

    public function addToProcessedData(ProcessedData $processedData): self
    {
        $this->processedDataList[] = $processedData;
        $this->processedDataListByType[$processedData->getType()->value()] ??= [];
        $this->processedDataListByType[$processedData->getType()->value()][] = $processedData;

        return $this;
    }

    /**
     * @param array|object $data
     */
    private function addData(string $dataStatus, $data): self
    {
        if ($type = DataTypeEnum::tryFrom($dataStatus)) {
            $this->addToProcessedData(new ProcessedData($type, $data));
        }

        return $this;
    }
}
