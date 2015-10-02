<?php namespace App\Http\Middleware;

use App\Model\Repo;
use App\Model\User;
use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class RepoAccess
 */
class RepoAccess
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


    public function handle(\Illuminate\Http\Request $request, Closure $next)
    {
        $repo = $request->route()->getParameter('repo');
        $user = $this->auth->user();

        if (!$user instanceof User) {
            abort(403);
        }

        if ($repo instanceof Repo && !$repo->canAccess($user)) {
            abort(403);
        }

        return $next($request);
    }
}
