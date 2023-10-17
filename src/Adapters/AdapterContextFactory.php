<?php

namespace OnrampLab\LaravelExceptions\Adapters;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Throwable;

class AdapterContextFactory
{
    public static function getAdapterContext(Throwable $exception): AdapterContext
    {
        if (is_null(Route::getCurrentRoute())) {
            /** @phpstan-ignore-next-line  */
            $arg = data_get(Request::server('argv'), 1);

            // NOTE: this is not working for testing due to there is no efficient way to know if
            //       it's running in a job
            if ($arg === 'horizon:work') {
                return new JobAdapterContext($exception);
            }
            return new ConsoleAdapterContext($exception);
        }

        return new WebAdapterContext($exception);
    }
}
