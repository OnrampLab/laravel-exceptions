<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit\Adapters;

use Exception;
use OnrampLab\LaravelExceptions\Adapters\JobAdapterContext;
use OnrampLab\LaravelExceptions\Tests\Unit\CustomApplicationException;
use OnrampLab\LaravelExceptions\Tests\TestCase;

class JobAdapterContextTest extends TestCase
{
    protected JobAdapterContext $context;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function getContext_should_work_for_general_Exception(): void
    {

        $e = new CustomApplicationException('Server is unstable', 'MySQL connection is timeout');
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

        $context = (new JobAdapterContext($exception))->getContext();

        $this->assertEquals([
            'type' => 'Job',
            'job' => 'CreateUser',
        ], $context);
    }
}
