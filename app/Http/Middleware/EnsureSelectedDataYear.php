<?php

namespace App\Http\Middleware;

use App\Models\Activity;
use App\Models\Archive;
use App\Models\Report;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSelectedDataYear
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        foreach ($request->route()->parameters() as $parameter) {
            if ($parameter instanceof Report || $parameter instanceof Activity || $parameter instanceof Archive) {
                abort_unless($parameter->newQuery()->forDataYear()->whereKey($parameter->getKey())->exists(), 404);
            }
        }

        return $next($request);
    }
}
