<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit\Adapters;

use Exception;
use Illuminate\Support\Facades\Request;
use OnrampLab\LaravelExceptions\Adapters\ConsoleAdapterContext;
use OnrampLab\LaravelExceptions\Tests\TestCase;

class ConsoleAdapterContextTest extends TestCase
{
    protected ConsoleAdapterContext $context;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function getContext_should_work_for_general_Exception(): void
    {
        $context = new ConsoleAdapterContext(new Exception('Test'));

        Request::spy();
        Request::shouldReceive('server')
            ->andReturn([
                'artisan',
                'test-command'
            ]);

        $array = $context->getContext();

        $this->assertEquals([
            'type' => 'Command',
            'command' => 'test-command',
        ], $array);
    }
}
