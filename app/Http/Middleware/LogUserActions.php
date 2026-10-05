<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Configurations\Configuration;
use Illuminate\Support\Facades\Cache;

class LogUserActions
{
    /**
     * Handle an incoming request and log user actions.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next The next middleware in the pipeline.
     * @return Response The HTTP response after logging user actions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $input = $request->path() === 'login' ? 'N/A' : $this->truncateInput($request->all() ?? []);

        if (!$this->isLoggingEnabled()) {
            return $response;
        }

        Log::channel('user_actions')->info('User action', [
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'user_name' => Auth::check() ? Auth::user()->name : null,
            'url' => $request->fullUrl() ?? 'N/A',
            'method' => $request->method() ?? 'N/A',
            'ip' => $request->ip() ?? 'N/A',
            'input' => $input,
            'user_agent' => $request->header('User-Agent') ?? 'N/A',
            'status_code' => $response->getStatusCode() ?? 'N/A',
            'referer' => $request->header('referer') ?? 'N/A',
        ]);

        return $response;
    }

    /**
     * Truncate input values that are too long to avoid excessive logging.
     *
     * @param array $input The input data to truncate.
     * @param int $maxLength The maximum length of input values.
     * @return array The truncated input data.
     */
    protected function truncateInput(array $input, int $maxLength = 10000): array
    {
        return array_map(function ($value) use ($maxLength) {
            if (is_string($value) && strlen($value) > $maxLength) {
                return substr($value, 0, $maxLength) . '...';
            }
            return $value;
        }, $input);
    }

    protected function isLoggingEnabled(): bool
    {
        return Cache::rememberForever('global_use_logs', function () {
            $config = Configuration::where('key', 'global_use_logs')->first();
            return $config ? $config->value === 'true' : false;
        });
    }
}
