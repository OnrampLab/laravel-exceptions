<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit;

use Closure;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Mockery;
use OnrampLab\LaravelExceptions\Handler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use OnrampLab\LaravelExceptions\Tests\TestCase;

class HandlerTest extends TestCase
{
    protected Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.debug', false);

        $this->handler = app(Handler::class);
    }

    /**
     * @test
     * @dataProvider reportDataProvider
     */
    public function report(Closure $getException, array $data, bool $shouldLog): void
    {
        Log::spy();
        Log::shouldReceive('error')
            ->times($shouldLog ? 1 : 0)
            ->withArgs(function ($message, $context) use ($data) {
                return $message === $data['message']
                    && $context['detail'] === $data['detail']
                    && isset($context['adapter'])
                    && isset($context['errors']);
            });

        $this->handler->report($getException());
    }

    /**
     * @test
     */
    public function report_for_request(): void
    {
        $route = Mockery::mock();
        $route->shouldReceive('uri')
            ->andReturn('/api/users/');

        Route::shouldReceive('getCurrentRoute')
            ->andReturn($route);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['adapter']['type'] === 'API'
                    && isset($context['user_id'])
                    && isset($context['errors']);
            });

        $this->handler->report(new CustomApplicationException('Custom Title', 'Custom Error', [
            'user_id' => 1,
        ], Response::HTTP_CONFLICT));
    }

    /**
     * @test
     */
    public function report_for_job(): void
    {
        Request::spy();

        Request::shouldReceive('server')
            ->andReturn([
                'artisan',
                'horizon:work'
            ]);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Unknown Error'
                    && $context['detail'] === 'Test'
                    && $context['adapter']['type'] === 'Job'
                    && $context['adapter']['job'] === 'CreateUser'
                    && isset($context['errors'][0]['stacktrace']);
            });

        $exception = new Exception('Test');
        $trace = [[
            'file' => '/var/www/html/app/Jobs/CreateUser.php',
            'line' => 80,
            'type' => '->',
            'function' => 'createUser',
            'class' => 'App\\Service\TestService'
        ]];
        $exceptionReflection = new \ReflectionObject($exception);
        $traceReflection = $exceptionReflection->getProperty('trace');
        $traceReflection->setAccessible(true);
        $traceReflection->setValue($exception, $trace);
        $traceReflection->setAccessible(false);

        $exceptionReflection = new \ReflectionObject($exception);
        $traceReflection = $exceptionReflection->getMethod('getTraceAsString');
        $traceReflection->setAccessible(true);

        $this->handler->report($exception);
    }

    /**
     * @test
     */
    public function report_should_log_warning_if_exception_is_in_warningReport()
    {
        Log::shouldReceive('warning')->once();

        $exceptionReflection = new \ReflectionObject($this->handler);
        $traceReflection = $exceptionReflection->getProperty('warningReport');
        $traceReflection->setAccessible(true);
        $traceReflection->setValue($this->handler, ['Exception']);
        $traceReflection->setAccessible(false);

        $exception = new Exception('Test');

        $this->handler->report($exception);
    }

    /**
     * @test
     * @dataProvider reportDataProvider
     */
    public function render(Closure $getException, array $data): void
    {
        $request = request();
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'application/json');

        /** @var JsonResponse */
        $response = $this->handler->render($request, $getException());

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertEquals([
            'errors' => [
                [
                    'status' => $data['status'],
                    'title' => $data['title'],
                    'detail' => $data['detail'],
                    'message' => $data['detail'],
                ]
            ]
        ], $response->getData(true));
        $this->assertEquals($data['status'], $response->getStatusCode());
    }

    public function reportDataProvider()
    {
        $shouldLog = true;
        $shouldNotLog = false;

        return [
            'authentication_exception_case' => [
                fn() => new AuthenticationException('Authentication required'),
                [
                    'title' => 'Need Authentication',
                    'detail' => 'Authentication required',
                    'status' => 401,
                ],
                $shouldNotLog,
            ],
            'access_deny_http_exception_case' => [
                fn() => new AccessDeniedHttpException('You have no access'),
                [
                    'title' => 'Forbidden',
                    'detail' => 'You have no access',
                    'status' => 403,
                ],
                $shouldNotLog,
            ],
            'model_not_found_exception_case' => [
                fn() => new ModelNotFoundException('Model not found'),
                [
                    'title' => 'Resource Not Found',
                    'detail' => 'Model not found',
                    'status' => 404,
                ],
                $shouldNotLog,
            ],
            'application_exception_case' => [
                fn() => new CustomApplicationException('Custom Title', 'Custom Error', [], Response::HTTP_CONFLICT),
                [
                    'title' => 'Custom Title',
                    'detail' => 'Custom Error',
                    'message' => 'Custom Title',
                    'status' => 500,
                ],
                $shouldLog,
            ],
            'validation_exception_case' => [
                // NOTE: if not using closure, it will be failed due to facade is not ready
                fn() => ValidationException::withMessages(['name' => 'name is required']),
                [
                    'title' => 'Invalid Attribute',
                    'detail' => 'name is required',
                    'status' => 422,
                ],
                $shouldNotLog,
            ],
            'exception_case' => [
                fn() => new Exception('Test'),
                [
                    'title' => 'Unknown Error',
                    'detail' => 'Test',
                    'message' => 'Unknown Error',
                    'status' => 500,
                ],
                $shouldLog,
            ],
        ];
    }
}
