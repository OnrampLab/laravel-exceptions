<?php

namespace OnrampLab\LaravelExceptions\Adapters;

use Throwable;

abstract class AdapterContext
{
    protected Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return array<string, mixed>
     */
    abstract public function getContext(): array;
}
