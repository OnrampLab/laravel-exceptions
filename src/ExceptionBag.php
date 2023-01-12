<?php

namespace OnrampLab\LaravelExceptions;

use OnrampLab\LaravelExceptions\Contracts\ApplicationException;
use Throwable;

class ExceptionBag
{
    private Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return array<Throwable>
     */
    public function getAllExceptions(): array
    {
        $e = $this->exception;
        $errors = [];

        do {
            $errors[] = $e;
            $e = $e->getPrevious();
        } while ($e);

        return $errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(?array $fields = null, bool $hideInternalDetail = false): array
    {
        $errors = $this->getAllExceptions();

        return array_map(
            static fn ($e) => (new ApplicationExceptionContext($e))->getContext($fields, $hideInternalDetail),
            $errors,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getExceptionContext(): array
    {
        $exceptions = $this->getAllExceptions();
        $errorContext = [];

        foreach ($exceptions as $e) {
            if ($e instanceof ApplicationException) {
                $errorContext = array_merge($errorContext, $e->context());
            }
        }

        return $errorContext;
    }
}
