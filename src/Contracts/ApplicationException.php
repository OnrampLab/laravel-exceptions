<?php

namespace OnrampLab\LaravelExceptions\Contracts;

interface ApplicationException
{
    /**
     * Returns the title.
     */
    public function getTitle(): string;

    /**
     * Returns detail.
     */
    public function getDetail(): string;

    /**
     * Returns context.
     *
     * @return array<string, mixed>
     */
    public function context(): array;
}
