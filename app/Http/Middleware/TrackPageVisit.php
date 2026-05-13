<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    /** Paths to skip tracking (assets, health checks, admin tracking itself). */
    private const SKIP_PREFIXES = [
        '/build/',
        '/favicon',
        '/up',
        '/_ignition',
        '/admin/visitors',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests that return HTML (not AJAX/JSON/assets)
        if (
            $request->isMethod('GET')
            && ! $request->expectsJson()
            && $response->isSuccessful()
            && ! $this->shouldSkip($request->path())
        ) {
            PageVisit::create([
                'user_id'    => $request->user()?->id,
                'path'       => '/'.$request->path(),
                'method'     => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'visited_at' => now(),
            ]);
        }

        return $response;
    }

    private function shouldSkip(string $path): bool
    {
        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with('/'.$path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
