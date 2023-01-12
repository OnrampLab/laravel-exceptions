<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit;

use Exception;
use OnrampLab\LaravelExceptions\LoggingContext;
use OnrampLab\LaravelExceptions\Tests\TestCase;

class LoggingContextTest extends TestCase
{
    /** @test */
    public function getContext_should_work_for_general_Exception(): void
    {
        $e2 = new Exception('I am e2');
        $e1 = new CustomApplicationException('I am e1 title', 'I am e1 detail', ['lead_id' => 2], 0, $e2);
        $initialContext = ['user_id' => 1];

        $context = (new LoggingContext($e1, $initialContext))->getContext();
        $errors = $context['errors'];

        $this->assertEquals(2, count($errors));
        $this->assertEquals('I am e1 detail', $errors[0]['detail']);
        $this->assertEquals('I am e2', $errors[1]['detail']);

        $this->assertEquals('I am e1 detail', $context['detail']);

        $this->assertEquals(1, $context['user_id']);
        $this->assertEquals(2, $context['lead_id']);

        $this->assertNotNull($context['adapter']);
    }
}
