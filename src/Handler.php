<?php

namespace OnrampLab\LaravelExceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Reflector;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<class-string<Throwable>>
     */
    protected $dontReport = [

    ];

    /** @var array<class-string> $internalDontReport */
    protected $internalDontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        MultipleRecordsFoundException::class,
        RecordsNotFoundException::class,
        SuspiciousOperationException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /** @var array<string> $applicationFolderNames */
    protected array $applicationFolderNames = [];

    /** @var array<class-string> $emergencyReport */
    protected array $emergencyReport = [];

    /** @var array<class-string> $alertReport */
    protected array $alertReport = [];

    /** @var array<class-string> $criticalReport */
    protected array $criticalReport = [];

    /** @var array<class-string> $errorReport */
    protected array $errorReport = [];

    /** @var array<class-string> $warningReport */
    protected array $warningReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e): void
    {
        // Copy from Laravel source code. It's better to keep it up to date
        $e = $this->mapException($e);

        if ($this->shouldntReport($e)) {
            return;
        }

        /** @phpstan-ignore-next-line  */
        if (Reflector::isCallable($reportCallable = [$e, 'report']) && $this->container->call($reportCallable) !== false) {
            return;
        }

        foreach ($this->reportCallbacks as $reportCallback) {
            if ($reportCallback->handles($e) && !$reportCallback($e)) {
                return;
            }
        }
        // End of Laravel Source Code

        $this->reportException($e);
    }

    protected function reportException(Throwable $e): void
    {
        $loggingContext = new LoggingContext($e, $this->context());
        $title = $loggingContext->getTitle();
        $context = $loggingContext->getContext();
        $logLevel = $this->getLogLevel($e);

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $logger->{$logLevel}($title, $context);
    }

    protected function getLogLevel(Throwable $e): string
    {
        $level = [
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
        ];

        $logLevel = collect($level)
            ->first(function ($method) use ($e) {
                $propertyName = $method . 'Report';
                $exceptionClassNames = $this->{$propertyName};

                return collect($exceptionClassNames)
                    ->first(fn ($exceptionClassName) => $this->isInstanceOfException($e, $exceptionClassName));
            });

        return $logLevel ?? 'error';
    }

    /**
     * Provider a way to let system to customize how to match the exception
     */
    protected function isInstanceOfException(Throwable $e, string $exceptionClassName): bool
    {
        return $e instanceof $exceptionClassName;
    }

    /**
     * Convert the given exception to an array.
     *
     * @return array<string, mixed>
     */
    protected function convertExceptionToArray(Throwable $e): array
    {
        return (new RenderingContext($e))->getContext();
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  Request  $request
     */
    protected function unauthenticated($request, AuthenticationException $exception): Response
    {
        return $this->shouldReturnJson($request, $exception)
                    /** @phpstan-ignore-next-line  */
                    ? response()->json($this->convertExceptionToArray($exception), 401)
                    /** @phpstan-ignore-next-line  */
                    : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  Request  $request
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        /** @phpstan-ignore-next-line  */
        return response()->json(
            $this->convertExceptionToArray($exception),
            $exception->status,
        );
    }
}
