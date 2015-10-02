<?php namespace App\Http\Middleware;

use Closure;

/**
 * Class StartSessionOnlyForInternals
 */
class StartSessionOnlyForInternals
{
    protected $only = [
        'internal/*'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        foreach ($this->only as $only) {
            if (!$request->is($only)) {
                config()->set('session.driver', 'array');
            }
        }

        return $next($request);
    }
}
