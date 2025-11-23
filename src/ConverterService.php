<?php

namespace Yurinskiy\RequestBundle;

use Symfony\Component\Serializer\SerializerInterface;

class ConverterService
{
    public const DISABLE_TYPE_ENFORCEMENT = 'disable_type_enforcement';
    public const OBJECT_TO_POPULATE = 'object_to_populate';

    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer )
    {
        $this->serializer = $serializer;
    }

    /**
     * @throws \LogicException
     */
    public function populate(array $data, string $className, ?object $object = null, array $deserializeOptions = []): object
    {
        if ($deserializeOptions) {
            $options = $deserializeOptions;
        } else {
            $options = [
                self::DISABLE_TYPE_ENFORCEMENT => true,
            ];
        }

        if (null !== $object) {
            $options[self::OBJECT_TO_POPULATE] = $object;
        }

        try {
            return $this->serializer->deserialize(json_encode($data, JSON_THROW_ON_ERROR), $className, 'json', $options);
        } catch (\Throwable $exception) {
            throw new \LogicException(sprintf('Converter::populate: Ошибка создания заполнения объекта %s', $className));
        }
    }

    /**
     * @throws \LogicException
     */
    public function objectToArray(object $object): array
    {
        try {
            $json = $this->serializer->serialize($object, 'json', ['json_encode_options' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES]);

            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new \LogicException(sprintf('Converter::populate: Ошибка преобразования %s в массив', get_class($object)));
        }
    }
}