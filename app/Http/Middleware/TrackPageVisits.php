<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldTrack($request)) {
            $visit = $this->recordVisit($request);
            $request->attributes->set('pageVisit', $visit);
        }

        return $next($request);
    }

    protected function shouldTrack(Request $request): bool
    {
        return $request->isMethod('get') && ! $request->expectsJson();
    }

    protected function recordVisit(Request $request): PageVisit
    {
        $path = '/' . ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        $routeName = $request->route()?->getName();

        $visit = PageVisit::query()->updateOrCreate(
            ['path' => $path],
            ['route_name' => $routeName]
        );

        $visit->increment('visits');
        $visit->refresh();
        $visit->forceFill([
            'route_name' => $routeName,
            'last_visited_at' => now(),
        ])->save();

        return $visit;
    }
}
