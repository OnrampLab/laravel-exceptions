<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Queue\MaxAttemptsExceededException;
use OnrampLab\LaravelExceptions\ApplicationExceptionContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OnrampLab\LaravelExceptions\Tests\TestCase;

class ApplicationExceptionContextTest extends TestCase
{
    protected ApplicationExceptionContext $context;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function getContext_should_work_for_general_Exception(): void
    {
        $e = new Exception('MySQL connection is timeout');
        $context = new ApplicationExceptionContext($e);
        $array = $context->getContext();

        $this->assertEquals('Unknown Error', $array['title']);
        $this->assertEquals('MySQL connection is timeout', $array['detail']);
        $this->assertStringStartsWith('##', $array['stacktrace'][0]);
        $this->assertStringStartsWith('#0', $array['stacktrace'][1]);
    }

    /** @test */
    public function getContext_should_work_for_ApplicationException(): void
    {
        $e = new CustomApplicationException('Server is unstable', 'MySQL connection is timeout');
        $context = new ApplicationExceptionContext($e);
        $array = $context->getContext();

        $this->assertEquals('Server is unstable', $array['title']);
        $this->assertEquals('MySQL connection is timeout', $array['detail']);
        $this->assertStringStartsWith('##', $array['stacktrace'][0]);
        $this->assertStringStartsWith('#0', $array['stacktrace'][1]);
    }

    /** @test */
    public function getContext_should_work_for_general_NotFoundHttpException(): void
    {
        $e = new NotFoundHttpException('Page Not Found');
        $context = new ApplicationExceptionContext($e);
        $array = $context->getContext();

        $this->assertEquals('Resource Not Found', $array['title']);
        $this->assertEquals('Page Not Found', $array['detail']);
        $this->assertStringStartsWith('##', $array['stacktrace'][0]);
        $this->assertStringStartsWith('#0', $array['stacktrace'][1]);
    }

    /** @test */
    public function getContext_should_work_for_general_AccessDeniedHttpException(): void
    {
        $e = new AccessDeniedHttpException('You have no access to user entity');
        $context = new ApplicationExceptionContext($e);
        $array = $context->getContext();

        $this->assertEquals('Forbidden', $array['title']);
        $this->assertEquals('You have no access to user entity', $array['detail']);
        $this->assertStringStartsWith('##', $array['stacktrace'][0]);
        $this->assertStringStartsWith('#0', $array['stacktrace'][1]);
    }

    /** @test */
    public function getContext_should_work_for_general_AuthenticationException(): void
    {
        $e = new AuthenticationException('You should login first');
        $context = new ApplicationExceptionContext($e);
        $array = $context->getContext();

        $this->assertEquals('Need Authentication', $array['title']);
        $this->assertEquals('You should login first', $array['detail']);
        $this->assertStringStartsWith('##', $array['stacktrace'][0]);
        $this->assertStringStartsWith('#0', $array['stacktrace'][1]);
    }

    /** @test */
    public function getContext_should_work_for_general_MaxAttemptsExceededException(): void
    {
        $e = new MaxAttemptsExceededException('OnrampLab\Webhooks\CallWebhookJob has been attempted too many times or run too long. The job may have previously timed out.');
        $context = new ApplicationExceptionContext($e);
        $array = $context->getContext();

        $this->assertEquals('Max Attempts Exceeded', $array['title']);
        $this->assertEquals('OnrampLab\Webhooks\CallWebhookJob has been attempted too many times or run too long. The job may have previously timed out.', $array['detail']);
        $this->assertStringStartsWith('##', $array['stacktrace'][0]);
        $this->assertStringStartsWith('#0', $array['stacktrace'][1]);
    }
}
