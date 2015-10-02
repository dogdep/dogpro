<?php namespace App\Http\Controllers;

use App\Ansible\Roles\RoleRepository;
use App\Http\Requests\Repo\CreateRepoRequest;
use App\Jobs\Repo\CloneRepoJob;
use App\Jobs\Repo\DeleteRepoJob;
use App\Jobs\Repo\UpdateRepoJob;
use App\Model\Repo;
use App\Model\User;
use Illuminate\Http\Request;

/**
 * Class RepoController
 */
class RepoController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->admin) {
            return Repo::all();
        } else {
            return $user->repos;
        }
    }

    public function roles()
    {
        return app(RoleRepository::class)->all();
    }

    public function get(Repo $repo)
    {
        return $repo->toArray();
    }

    /**
     * @param CreateRepoRequest $request
     * @return Repo
     */
    public function create(CreateRepoRequest $request)
    {
        $repo = Repo::create([
            'url' => $request->get('url'),
            'name' => $request->get('name'),
            'group' => $request->get('group'),
        ]);

        $repo->users()->save(auth()->user());

        $this->dispatch(new CloneRepoJob($repo));

        return $repo;
    }

    public function update(Repo $repo, Request $request)
    {
        $repo->params = $request->get('params');
        $repo->save();
        return $repo;
    }

    /**
     * @param Repo $repo
     * @return Repo
     */
    public function delete(Repo $repo)
    {
        if (!auth()->user()->admin) {
            return response()->json(["error"=>"Only admin can delete repository"], 422);
        }
        $this->dispatch(new DeleteRepoJob($repo));

        return $repo;
    }

    /**
     * @param Repo $repo
     * @param string $hash
     * @return array
     */
    public function commit(Repo $repo, $hash)
    {
        if (!$repo->isCloned()) {
            return [];
        }

        return $repo->commits()->get($hash, true);
    }

    /**
     * @param Repo $repo
     * @param int $page
     * @return array
     */
    public function commits(Repo $repo, $page = 1)
    {
        if (!$repo->isCloned()) {
            return [];
        }

        $result = [];

        foreach ($repo->commits()->paginate(null, $page)->toArray() as $commit) {
            $result[] = $commit + [
                'release' => $repo->releases()->where('commit', $commit['hash'])->first()
            ];
        }


        return $result;
    }

    /**
     * Update repo commit log from GitLab
     *
     * @param Repo $repo
     * @return Repo
     */
    public function pull(Repo $repo)
    {
        $this->dispatch(new UpdateRepoJob($repo, []));

        return $repo;
    }

    public function postUser(Repo $repo, User $user)
    {
        $repo->users()->attach($user->id);
        return $repo;
    }

    public function deleteUser(Repo $repo, User $user)
    {
        $repo->users()->detach($user);
        return $repo;
    }
}
