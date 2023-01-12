<?php

namespace OnrampLab\LaravelExceptions;

use OnrampLab\LaravelExceptions\Adapters\AdapterContextFactory;
use Throwable;

class LoggingContext
{
    /**
     * @param array<string, mixed> $initialContext
     */
    public function __construct(
        private Throwable $exception,
        private array $initialContext
    ) {
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function getTitle(): string
    {
        return (new ApplicationExceptionContext($this->exception))->getTitle();
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        $exceptionBag = new ExceptionBag($this->exception);
        $errorContext = $exceptionBag->getExceptionContext();
        $fields = [
            ApplicationExceptionContext::FIELD_TITLE,
            ApplicationExceptionContext::FIELD_DETAIL,
            ApplicationExceptionContext::FIELD_EXCEPTION_CLASS,
            ApplicationExceptionContext::FIELD_STACKTRACE,
        ];

        return [
            'detail' => (new ApplicationExceptionContext($this->exception))->getDetail(false),
            'adapter' => AdapterContextFactory::getAdapterContext($this->exception)->getContext(),
            'errors' => $exceptionBag->getContext($fields, false),
            ...$this->initialContext,
            ...$errorContext,
        ];
    }
}
