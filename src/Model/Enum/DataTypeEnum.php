<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Model\Enum;

/**
 * Class DataTypeEnum.
 *
 * Usage:
 *   $type = DataTypeEnum::REQUEST();
 *   echo $type->label(); // "Данные для запроса"
 *
 * @method static self REQUEST()
 * @method static self RESPONSE()
 * @method static self ERROR()
 * @method static self VALIDATION()
 */
final class DataTypeEnum
{
    public const REQUEST = 'request';
    public const RESPONSE = 'response';
    public const ERROR = 'error';
    public const VALIDATION = 'validation';

    private const ALLOWED_VALUES = [
        self::REQUEST,
        self::RESPONSE,
        self::ERROR,
        self::VALIDATION,
    ];

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, self::ALLOWED_VALUES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid DataTypeEnum value: "%s"', $value));
        }
        $this->value = $value;
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public static function tryFrom(string $value): ?self
    {
        return in_array($value, self::ALLOWED_VALUES, true) ? new self($value) : null;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::REQUEST:
                return 'Данные для запроса';
            case self::RESPONSE:
                return 'Данные ответа';
            case self::ERROR:
                return 'Ошибка';
            case self::VALIDATION:
                return 'Ошибка валидации данных';
            default:
                // Should never happen due to constructor validation
                throw new \LogicException('Unexpected DataTypeEnum value: '.$this->value);
        }
    }

    /**
     * Magic static factory methods: REQUEST(), RESPONSE(), etc.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return self
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($name, $arguments)
    {
        switch ($name) {
            case 'REQUEST':
                return new self(self::REQUEST);
            case 'RESPONSE':
                return new self(self::RESPONSE);
            case 'ERROR':
                return new self(self::ERROR);
            case 'VALIDATION':
                return new self(self::VALIDATION);
            default:
                throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', __CLASS__, $name));
        }
    }
}
