<?php

declare(strict_types=1);

namespace Yurinskiy\RequestBundle\Traits;

use Carbon\CarbonImmutable;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Yurinskiy\Context\ContextBucket;
use Yurinskiy\RequestBundle\Context\Filter\CodeContext;
use Yurinskiy\RequestBundle\Context\Option\Repeater\AmqpGroupContext;
use Yurinskiy\RequestBundle\Context\Option\Repeater\AmqpPriorityContext;
use Yurinskiy\RequestBundle\Context\Option\Repeater\CurrentRetryCountContext;
use Yurinskiy\RequestBundle\Context\Option\Repeater\ErrorRepeatContext;
use Yurinskiy\RequestBundle\Context\Option\Repeater\ExpiredDateContext;
use Yurinskiy\RequestBundle\Context\Option\Repeater\RetryCountContext;
use Yurinskiy\RequestBundle\Context\Option\Repeater\WaitRepeatContext;
use Yurinskiy\RequestBundle\Context\UuidContext;
use Yurinskiy\RequestBundle\Model\RequestProcessorModel;
use Yurinskiy\RequestBundle\PayloadMessage;
use Yurinskiy\RequestBundle\PayloadMessageInterface;

trait RepeaterTrait
{
    protected MessageBusInterface $messageBus;

    abstract protected function getRepeatTimeMs(): int;

    abstract protected function getErrorRepeatTimeMs(): int;

    abstract protected function getRetryCount(): int;

    abstract protected function getExpiredDate(): ?\DateTimeInterface;

    /**
     * @required
     */
    public function setMessageBus(MessageBusInterface $messageBus): void
    {
        $this->messageBus = $messageBus;
    }

    protected function initRepeaterHandlerOption(RequestProcessorModel $model): void
    {
        $optionContextBucket = $model->getOptions();

        if ($this->getRepeatTimeMs() && !$optionContextBucket->has(WaitRepeatContext::class)) {
            $optionContextBucket->add(new WaitRepeatContext($this->getRepeatTimeMs()));
        }

        if ($this->getErrorRepeatTimeMs() && !$optionContextBucket->has(ErrorRepeatContext::class)) {
            $optionContextBucket->add(new ErrorRepeatContext($this->getErrorRepeatTimeMs()));
        }

        if ($this->getExpiredDate() && !$optionContextBucket->has(ExpiredDateContext::class)) {
            $optionContextBucket->add(new ExpiredDateContext($this->getExpiredDate()));
        }

        if ($this->getRetryCount() && !$optionContextBucket->has(RetryCountContext::class)) {
            $optionContextBucket->add(new RetryCountContext($this->getRetryCount()));
        }

        if ($optionContextBucket->has(RetryCountContext::class) && !$optionContextBucket->has(CurrentRetryCountContext::class)) {
            $optionContextBucket->add(new CurrentRetryCountContext());
        }

        if (!$optionContextBucket->has(AmqpGroupContext::class)) {
            $optionContextBucket->add(new AmqpGroupContext());
        }

        if (!$optionContextBucket->has(AmqpPriorityContext::class)) {
            $optionContextBucket->add(new AmqpPriorityContext());
        }
    }

    public function isNeedSend(RequestProcessorModel $model): bool
    {
        $this->initRepeaterHandlerOption($model);

        if ($model->getStatus()->isComplete()) {
            return false;
        }

        $flagExpiredDate = $this->checkExpiredDate($model);

        if (null !== $flagExpiredDate) {
            return $flagExpiredDate;
        }

        $flagRetryCount = $this->checkRetryCount($model);

        if (null !== $flagRetryCount) {
            return $flagRetryCount;
        }

        $model->setStatusFailed()
            ->addDataError(
                ['Не найдены настройки логики повтора']
            );

        return false;
    }

    protected function checkExpiredDate(RequestProcessorModel $model): ?bool
    {
        $expiredDateContext = $model->getOptions()->last(ExpiredDateContext::class);

        if ($expiredDateContext instanceof ExpiredDateContext) {
            $expiredDate = $expiredDateContext->getExpiredDate();
            if (CarbonImmutable::now()->gte($expiredDate)) {
                $model->setStatusFailed()
                    ->addDataError(
                        [sprintf('Дата ожидания ответа истекла, останавливаем. Дата ожидания %s.', $expiredDate->format('d.m.Y H:i:s'))]
                    );

                return false;
            }

            return true;
        }

        return null;
    }

    protected function checkRetryCount(RequestProcessorModel $model): ?bool
    {
        $retryCountContext = $model->getOptions()->last(RetryCountContext::class);
        $currentRetryCountContext = $model->getOptions()->last(CurrentRetryCountContext::class);

        if ($retryCountContext instanceof RetryCountContext && $currentRetryCountContext instanceof CurrentRetryCountContext) {
            if ($currentRetryCountContext->getCount() >= $retryCountContext->getRetryCount()) {
                $model->setStatusFailed()
                    ->addDataError(
                        [sprintf('Количество попыток дошло до %s. Останавливаем', $retryCountContext->getRetryCount())]
                    );

                return false;
            }

            $currentRetryCountContext->addRetry();

            return true;
        }

        return null;
    }

    public function send(RequestProcessorModel $model): void
    {
        $stamps = $this->getStamps($model);
        $message = $this->buildMessage($model);
        $this->messageBus->dispatch($message, $stamps);
    }

    private function getStamps(RequestProcessorModel $model): array
    {
        /** @var AmqpGroupContext $amqpGroupContext */
        $amqpGroupContext = $model->getOptions()->last(AmqpGroupContext::class);

        /** @var AmqpPriorityContext $amqpPriorityContext */
        $amqpPriorityContext = $model->getOptions()->last(AmqpPriorityContext::class);

        if (!($amqpGroupContext instanceof AmqpGroupContext) || !($amqpPriorityContext instanceof AmqpPriorityContext)) {
            throw new \Exception('AmqpGroupContext and AmqpPriorityContext must be set');
        }

        $stamps[] = new AmqpStamp(
            $amqpGroupContext->getGroup(),
            AMQP_NOPARAM,
            ['priority' => $amqpPriorityContext->getPriority()]
        );

        $delay = $this->getDelay($model);
        if ($delay) {
            $stamps[] = new DelayStamp($delay);
        }

        return $stamps;
    }

    private function getDelay(RequestProcessorModel $model): int
    {
        if ($model->getStatus()->isStatusFailed()) {
            /** @var ErrorRepeatContext|null $errorRepeatContext */
            $errorRepeatContext = $model->getOptions()->last(ErrorRepeatContext::class);

            return $errorRepeatContext->getErrorRepeatMs();
        }

        /** @var WaitRepeatContext|null $waitRepeatContext */
        $waitRepeatContext = $model->getOptions()->last(WaitRepeatContext::class);

        return $waitRepeatContext->getWaitTimeMs();
    }

    private function buildMessage(RequestProcessorModel $model): PayloadMessageInterface
    {
        return new PayloadMessage(
            [],
            new ContextBucket(
                new UuidContext($model->getUuid()),
                new CodeContext($model->getCode())
            )
        );
    }
}
