<?php namespace App\Http\Controllers;

use App\Http\Requests\Release\CreateRelease;
use App\Jobs\Release\PrepareReleaseJob;
use App\Model\Release;
use Illuminate\Http\Request;

/**
 * Class ReleaseController
 */
class ReleaseController extends Controller
{
    /**
     * @param CreateRelease $request
     * @return Release
     */
    public function create(CreateRelease $request)
    {
        $repo = $request->repo();
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
            'params' => $request->get('params'),
        ]);

        $this->dispatch(new PrepareReleaseJob($release));

        return $release;
    }

    public function update(Release $release, Request $request)
    {
        switch ($request->get('status')) {
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

    public function all(Request $request)
    {
        return Release::query()->where('repo_id', $request->get('repo_id'))->paginate()->items();
    }

    public function get(Release $release)
    {
        return $release->toArray() + [
            'time_avg' => $release->avg(),
        ];
    }
}
