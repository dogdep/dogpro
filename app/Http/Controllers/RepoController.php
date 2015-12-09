<?php namespace App\Http\Controllers;

use App\Ansible\Roles\RoleRepository;
use App\Config\DogproConfig;
use App\Http\Requests\Repo\CreateRepoRequest;
use App\Jobs\Repo\CloneRepoJob;
use App\Jobs\Repo\DeleteRepoJob;
use App\Jobs\Repo\UpdateRepoJob;
use App\Model\Repo;
use App\Model\User;
use Gitonomy\Git\Blob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class RepoController
 */
class RepoController extends Controller
{
    /**
     * @return Repo[]
     */
    public function index()
    {
        if ($this->user()->admin) {
            return Repo::query()->ordered()->get();
        } else {
            return $this->user()->repos;
        }
    }

    /**
     * @param RoleRepository $repository
     * @return array
     */
    public function roles(RoleRepository $repository)
    {
        return $repository->all();
    }

    /**
     * @param Repo $repo
     * @return Repo
     */
    public function get(Repo $repo)
    {
        return $repo;
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

        if (!is_null($this->user())) {
            $repo->users()->save($this->user());
        }

        $this->dispatch(new CloneRepoJob($repo));

        return $repo;
    }

    /**
     * @param Repo $repo
     * @param Request $request
     * @return Repo
     */
    public function update(Repo $repo, Request $request)
    {
        $repo->fill($request->only('params'))->save();

        return $repo;
    }

    /**
     * @param Repo $repo
     * @return Repo|\Illuminate\Http\JsonResponse
     */
    public function delete(Repo $repo)
    {
        if (!$this->user()->admin) {
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

    /**
     * @param Repo $repo
     * @param User $user
     * @return Repo
     */
    public function postUser(Repo $repo, User $user)
    {
        $repo->users()->attach($user->id);
        return $repo;
    }

    /**
     * @param Repo $repo
     * @param User $user
     * @return Repo
     */
    public function deleteUser(Repo $repo, User $user)
    {
        $repo->users()->detach($user->id);
        return $repo;
    }

    /**
     * @param Repo $repo
     * @param string $commit
     * @return DogproConfig|JsonResponse
     */
    public function config(Repo $repo, $commit)
    {
        $commit = $repo->git()->getCommit($commit);

        if (!$commit) {
            abort(404);
        }

        try {
            $tree = $commit->getTree()->getEntry(DogproConfig::FILENAME);
            if ($tree instanceof Blob) {
                return new DogproConfig($tree->getContent());
            }
        } catch (InvalidArgumentException $e) {
            return response()->json(['error'=>'Configuration file not found!'], 422);
        } catch (ParseException $e) {
            return response()->json(['error'=>'Could not parse configuration file: ' . $e->getMessage()], 422);
        }

        return new JsonResponse(new \stdClass());
    }
}
