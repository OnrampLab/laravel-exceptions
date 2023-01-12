<?php

namespace OnrampLab\LaravelExceptions\Adapters;

use Illuminate\Support\Facades\Request;

class ConsoleAdapterContext extends AdapterContext
{
    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        $args = Request::server('argv');
        $arg = $args[1] ?? 'Unknown Command';

        return [
            'type' => 'Command',
            'command' => $arg,
        ];
    }
}
