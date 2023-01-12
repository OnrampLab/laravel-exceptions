<?php

namespace OnrampLab\LaravelExceptions\Tests\Unit;

use Exception;
use Illuminate\Http\Response;
use OnrampLab\LaravelExceptions\Contracts\ApplicationException;
use Throwable;

class CustomApplicationException extends Exception implements ApplicationException
{
    protected string $title;
    protected array $context;

    public function __construct(string $title, string $detail, array $context = [], int $code = Response::HTTP_INTERNAL_SERVER_ERROR, Throwable $previous = null)
    {
        parent::__construct($detail, $code, $previous);

        $this->title = $title;
        $this->context = $context;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDetail(): string
    {
        return $this->getMessage();
    }
    public function context(): array
    {
        return $this->context;
    }
}
