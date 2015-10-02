<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class AdminOnly
 */
class AdminOnly
{
    /**
     * @var Guard
     */
    private $auth;

    /**
     * @param Guard $auth
     */
    function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }


    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->user() && $this->auth->user()->admin) {
            return $next($request);
        }

        return response()->json(["error"=>"You cannot access this resource"], 403);
    }
}
