<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit;

use Exception;
use Illuminate\Support\Facades\Config;
use OnrampLab\LaravelExceptions\RenderingContext;
use OnrampLab\LaravelExceptions\Tests\TestCase;

class RenderingContextTest extends TestCase
{
    /** @test */
    public function getContext_should_work_for_general_Exception(): void
    {
        Config::set('app.debug', false);

        $e2 = new Exception('I am e2');
        $e1 = new CustomApplicationException('I am e1 title', 'I am e1 detail', ['lead_id' => 2], 0, $e2);
        $initialContext = ['user_id' => 1];

        $context = (new RenderingContext($e1, $initialContext))->getContext();
        $error = $context['errors'][0];

        $this->assertEquals(1, count($context['errors']));
        $this->assertEquals('I am e1 title', $error['title']);
        $this->assertEquals('I am e1 detail', $error['detail']);
    }
}
