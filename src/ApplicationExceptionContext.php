<?php

namespace OnrampLab\LaravelExceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;
use Illuminate\Queue\MaxAttemptsExceededException;
use OnrampLab\LaravelExceptions\Contracts\ApplicationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApplicationExceptionContext
{
    public const FIELD_TITLE = 'title';
    public const FIELD_DETAIL = 'detail';
    public const FIELD_MESSAGE = 'message';
    public const FIELD_STATUS = 'status';
    public const FIELD_EXCEPTION_CLASS = 'exception_class';
    public const FIELD_STACKTRACE = 'stacktrace';

    private Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function getTitle(): string
    {
        $e = $this->exception;
        $title = 'Unknown Error';

        if ($e instanceof HttpExceptionInterface) {
            if ($e instanceof NotFoundHttpException) {
                $title = 'Resource Not Found';
            } elseif ($e instanceof AccessDeniedHttpException) {
                $title = 'Forbidden';
            }
        } elseif ($e instanceof AuthenticationException) {
            $title = 'Need Authentication';
        } elseif ($e instanceof MaxAttemptsExceededException) {
            $title = 'Max Attempts Exceeded';
        }

        if ($e instanceof ApplicationException) {
            $title = $e->getTitle();
        }

        return $title;
    }

    public function getDetail(bool $hideInternalDetail): string
    {
        $e = $this->exception;
        $detail = $e->getMessage();

        if ($e instanceof ApplicationException) {
            $detail = $e->getDetail();
        }

        $hideDetail = $e instanceof HttpExceptionInterface
            && $hideInternalDetail
            && $e->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($hideDetail) {
            $detail = 'Server Error';
        }

        return $detail;
    }

    public function getStatus(): int
    {
        $e = $this->exception;
        $status = 500;

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
        } elseif ($e instanceof AuthenticationException) {
            $status = 401;
        }

        return $status;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(?array $fields = null, bool $hideInternalDetail = false): array
    {
        $e = $this->exception;
        $detail = $this->getDetail($hideInternalDetail);

        $array = [
            self::FIELD_TITLE => $this->getTitle(),
            self::FIELD_DETAIL => $detail,
            self::FIELD_MESSAGE => $detail,
            self::FIELD_STATUS => $this->getStatus(),
            self::FIELD_EXCEPTION_CLASS => $e::class,
            self::FIELD_STACKTRACE => $this->getFullStacktrace(),
        ];

        if ($fields) {
            $array = collect($array)->only($fields)->toArray();
        }

        if (method_exists($e, 'context')) {
            return [
                ...$array,
                ...$e->context(),
            ];
        }

        return $array;
    }

    /**
     * @return array<string>
     */
    private function getFullStacktrace(): array
    {
        $e = $this->exception;
        $stackItems = explode("\n", $e->getTraceAsString());
        $error_string = '## ' . $e->getFile() . '(' . $e->getLine() . ')';

        $lastIndex = 0;

        collect($stackItems)->last(static function ($item, $index) use (&$lastIndex) {
            if (strpos($item, 'app') && $index < 5) {
                $lastIndex = $index;

                return true;
            }
        });

        return [...[$error_string], ...array_slice($stackItems, 0, $lastIndex + 1)];
    }
}
