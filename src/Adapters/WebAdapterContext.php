<?php

namespace OnrampLab\LaravelExceptions\Adapters;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class WebAdapterContext extends AdapterContext
{
    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'type' => 'API',
            'route' => Route::getCurrentRoute()?->uri(),
            'method' => Request::method(),
            'url' => Request::fullUrl(),
            'input' => Request::except(['password']),
        ];
    }
}
