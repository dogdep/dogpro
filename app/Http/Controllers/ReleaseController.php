<?php namespace App\Http\Controllers;

use App\Config\DogproConfig;
use App\Http\Requests\Release\CreateRelease;
use App\Jobs\Release\PrepareReleaseJob;
use App\Model\Release;
use App\Model\Repo;
use Gitonomy\Git\Blob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class ReleaseController
 */
class ReleaseController extends Controller
{
    /**
     * @param CreateRelease $request
     * @param Repo $repo
     * @return Repo
     */
    public function create(CreateRelease $request, Repo $repo)
    {
        $commit = $repo->git()->getCommit($request->get('commit'));

        if (!$commit) {
            abort(404);
        }

        $release = Release::create([
            'repo_id' => $repo->id,
            'commit' => $commit->getHash(),
            'status' => Release::QUEUED,
            'roles' => $request->roles(),
            'inventory_id' => $request->get('inventory_id'),
            'user_id' => $this->user()->id,
        ]);

        $this->dispatch(new PrepareReleaseJob($release));

        return $release;
    }

    public function update(Repo $repo, Release $release, Request $request)
    {
        switch($request->get('status')) {
            case Release::QUEUED:
                if (!$release->status == Release::ERROR) {
                    return response()->json(['error'=>'Cannot retry!'], 422);
                }
                $release->update(['status' => Release::QUEUED]);
                $this->dispatch(new PrepareReleaseJob($release));
                break;
            case Release::CANCELLED:
                if (!$release->isCancellable()) {
                    return response()->json(['error'=>'Cannot cancel!'], 422);
                }
                $release->update(['status'=>Release::CANCELLED]);
                break;
        }

        return $release;
    }

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

    public function all(Repo $repo)
    {
        return iterator_to_array($repo->releases()->paginate());
    }

    public function get(Repo $repo, Release $release)
    {
        return $release->toArray() + [
            'time_avg' => $release->avg(),
        ];
    }
}
