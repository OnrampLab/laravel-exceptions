<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit\Adapters;

use Exception;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Mockery;
use OnrampLab\LaravelExceptions\Adapters\WebAdapterContext;
use OnrampLab\LaravelExceptions\Tests\Unit\CustomApplicationException;
use OnrampLab\LaravelExceptions\Tests\TestCase;

class WebAdapterContextTest extends TestCase
{
    protected WebAdapterContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new WebAdapterContext(new Exception('Test'));
    }

    /** @test */
    public function getContext_should_work_for_general_Exception(): void
    {
        $e = new CustomApplicationException('Server is unstable', 'MySQL connection is timeout');

        $expected = [
            'type' => 'API',
            'route' => 'users',
            'method' => 'POST',
            'url' => 'http://localhost:8000/users',
            'input' => [
                'first_name' => 'Kevin',
                'last_name' => 'Garnett',
            ],
        ];

        $route = Mockery::mock();
        $route->shouldReceive('uri')->andReturn($expected['route']);
        Route::shouldReceive('getCurrentRoute')->andReturn($route);

        Request::spy();
        Request::shouldReceive('method')->andReturn($expected['method']);
        Request::shouldReceive('fullUrl')->andReturn($expected['url']);
        Request::shouldReceive('except')->andReturn($expected['input']);

        $array = $this->context->getContext();

        $this->assertEquals($expected, $array);
    }
}
