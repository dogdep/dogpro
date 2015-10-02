<?php namespace App\Http\Controllers;

use App\Jobs\Repo\UpdateRepoJob;
use App\Model\Repo;
use Illuminate\Http\Request;

/**
 * Class HookController
 */
class HookController extends Controller
{
    /**
     * @param Repo $repo
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pull(Repo $repo, Request $request)
    {
        $this->dispatch(new UpdateRepoJob($repo, $request->json('commits')));

        return response("ok");
    }
}
