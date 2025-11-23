<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Model\Enum;

/**
 * Class StatusEnum.
 *
 * Usage:
 *   $status = StatusEnum::SUCCESS();
 *   $status = StatusEnum::from('wait');
 *
 * Static helpers:
 *   StatusEnum::isStatusSuccess('success') → true
 *   StatusEnum::isStatusNew('new') → true
 *
 * @method static self SUCCESS()
 * @method static self FAILED()
 * @method static self WAIT()
 * @method static self NEW()
 * @method static bool isStatusSuccess(string $status)
 * @method static bool isStatusFailed(string $status)
 * @method static bool isStatusWait(string $status)
 * @method static bool isStatusNew(string $status)
 * @method        bool isStatusSuccess()
 * @method        bool isStatusFailed()
 * @method        bool isStatusWait()
 * @method        bool isStatusNew()
 */
final class StatusEnum
{
    public const SUCCESS = 'success';
    public const FAILED = 'failed';
    public const WAIT = 'wait';
    public const NEW = 'new';

    /**
     * @var string[]
     */
    private const ALLOWED_VALUES = [
        self::SUCCESS,
        self::FAILED,
        self::WAIT,
        self::NEW,
    ];

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, self::ALLOWED_VALUES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid status value: %s', $value));
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
            case self::NEW:
                return 'Новый запрос';
            case self::FAILED:
                return 'Ошибочный запрос';
            case self::WAIT:
                return 'Запрос ожидает ответ';
            case self::SUCCESS:
                return 'Успешный запрос';
            default:
                // This should never happen due to constructor validation
                throw new \LogicException('Unexpected status value: '.$this->value);
        }
    }

    public function is(self $status): bool
    {
        return $this->value === $status->value();
    }

    public function isComplete(): bool
    {
        return $this->is(self::from(self::SUCCESS)) || $this->is(self::from(self::FAILED));
    }

    /**
     * @return self|bool
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // Сначала обрабатываем isStatusXyz()
        if ('isStatus' === substr($name, 0, 8) && strlen($name) > 8) {
            if (1 !== count($arguments) || !is_string($arguments[0])) {
                throw new \BadMethodCallException('Method '.$name.' requires exactly one string argument.');
            }

            $expected = strtolower(lcfirst(substr($name, 8)));
            $given = $arguments[0];

            return $given === $expected;
        }

        // Затем — фабричные методы: SUCCESS(), FAILED() и т.д.
        $constantName = strtoupper($name);
        if (defined('self::'.$constantName) && in_array(constant('self::'.$constantName), self::ALLOWED_VALUES, true)) {
            return new self(constant('self::'.$constantName));
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', __CLASS__, $name));
    }

    /**
     * Magic method for $status->isStatusFailed(), etc.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return bool
     *
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        if ('isStatus' === substr($name, 0, 8) && strlen($name) > 8) {
            if (!empty($arguments)) {
                throw new \BadMethodCallException('Method '.$name.' does not accept any arguments.');
            }

            $expectedConstantName = strtoupper(lcfirst(substr($name, 8)));
            $expectedValue = null;

            // Map method name to constant value
            switch ($expectedConstantName) {
                case 'NEW':
                    $expectedValue = self::NEW;
                    break;
                case 'SUCCESS':
                    $expectedValue = self::SUCCESS;
                    break;
                case 'FAILED':
                    $expectedValue = self::FAILED;
                    break;
                case 'WAIT':
                    $expectedValue = self::WAIT;
                    break;
                default:
                    throw new \BadMethodCallException(sprintf('Unknown status in method name: %s', $name));
            }

            return $this->value === $expectedValue;
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', __CLASS__, $name));
    }
}
