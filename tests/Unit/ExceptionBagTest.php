<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit;

use Exception;
use OnrampLab\LaravelExceptions\ExceptionBag;
use OnrampLab\LaravelExceptions\Tests\TestCase;

class ExceptionBagTest extends TestCase
{
    /** @test */
    public function getContext_should_work_for_general_Exception(): void
    {
        $e2 = new Exception('I am e2');
        $e1 = new Exception('I am e1', 0, $e2);

        $array = (new ExceptionBag($e1))->getContext();

        $this->assertEquals(2, count($array));
        $this->assertEquals('I am e1', $array[0]['detail']);
        $this->assertEquals('I am e2', $array[1]['detail']);
    }
}
