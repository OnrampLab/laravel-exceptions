<?php

namespace OnrampLab\LaravelExceptions\Adapters;

use Throwable;

class JobAdapterContext extends AdapterContext
{
    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        $e = $this->exception;

        do {
            $jobName = $this->getJobName($e);
            $e = $e->getPrevious();
        } while (! $jobName && $e);

        return [
            'type' => 'Job',
            'job' => $jobName,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getApplicationFolderNames(): array
    {
        // TODO: should pass from outside
        return [
            'app',
        ];
    }

    private function getJobName(Throwable $exception): ?string
    {
        $stackItems = explode("\n", $exception->getTraceAsString());

        $regex = implode('|', $this->getApplicationFolderNames());

        $text = collect($stackItems)->last(static fn ($item) => strpos($item, $regex));

        if (! $text) {
            return null;
        }

        preg_match('/([^\/]+)\.php/', (string) $text, $matches);

        return $matches[1];
    }
}
