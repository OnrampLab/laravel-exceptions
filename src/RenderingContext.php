<?php

namespace OnrampLab\LaravelExceptions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Throwable;

class RenderingContext
{
    private Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return array{errors: mixed}
     */
    public function getContext(): array
    {
        if ($this->exception instanceof ValidationException) {
            return $this->getValidationExceptionContext();
        }

        return $this->getNormalExceptionContext();
    }

    /**
     * @return array{errors: mixed}
     */
    public function getNormalExceptionContext(): array
    {
        $e = $this->exception;
        $context = new ApplicationExceptionContext($e);
        $debug = config('app.debug');
        $debugInfo = $this->getDebugInfo($debug);

        $fields = [
            ApplicationExceptionContext::FIELD_TITLE,
            ApplicationExceptionContext::FIELD_DETAIL,
            ApplicationExceptionContext::FIELD_MESSAGE,
            ApplicationExceptionContext::FIELD_STATUS,
        ];

        return [
            'errors' => [
                [
                    ...$context->getContext($fields, ! $debug),
                    ...$debugInfo,
                ],
            ],
        ];
    }

    /**
     * @return array{errors: mixed}
     */
    public function getValidationExceptionContext(): array
    {
        /** @var ValidationException $exception */
        $exception = $this->exception;

        $errors = collect($exception->validator->errors()->all())->map(static fn ($message) => [
            'status' => $exception->status,
            'title' => 'Invalid Attribute',
            'message' => $message,
            'detail' => $message,
        ]);

        return [
            'errors' => $errors,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getDebugInfo(bool $debug): array
    {
        $e = $this->exception;
        $debugInfo = [];

        if ($debug) {
            $debugInfo = [
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(static fn ($trace) => Arr::except($trace, ['args']))->all(),
            ];
        }

        return $debugInfo;
    }
}
