<?php namespace App\Http\Middleware;

use App\Model\User;
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
    public function __construct(Guard $auth)
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
        $user = $this->auth->user();

        if ($user instanceof User && $user->admin) {
            return $next($request);
        }

        return response()->json(["error"=>"You cannot access this resource"], 403);
    }
}
