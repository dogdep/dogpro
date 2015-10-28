<?php namespace App\Http\Middleware;

use App\Model\Release;
use App\Model\Repo;
use App\Model\User;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

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

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $this->auth->user();
        /** @var \Illuminate\Routing\Route $route */
        $route = $request->route();

        if ($route->hasParameter("repo")) {
            $repo = $request->route()->getParameter('repo');
            if (!$this->checkRepoAccess($user, $repo)) {
                abort(403);
            }
        } elseif ($route->hasParameter("release")) {
            $release = $request->route()->getParameter('release');
            if ($release instanceof Release) {
                if (!$this->checkRepoAccess($user, $release->repo)) {
                    abort(403);
                }
            }
        }

        return $next($request);
    }

    protected function checkRepoAccess($user, $repo)
    {
        return $user instanceof User && $repo instanceof Repo && $repo->canAccess($user);
    }
}
