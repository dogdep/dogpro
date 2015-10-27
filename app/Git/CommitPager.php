<?php namespace App\Git;

use Gitonomy\Git\Commit;
use Gitonomy\Git\Log;
use Gitonomy\Git\Repository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Class CommitPager
 */
class CommitPager implements \JsonSerializable, Jsonable, Arrayable
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param $revision
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function paginate($revision = null, $page = 1, $perPage = 10)
    {
        $this->log = $this->repository->getLog($revision, null, ($page - 1) * $perPage, $perPage);
        return $this;
    }

    public function get($hash, $array = false)
    {
        $commit = $this->repository->getCommit($hash);

        if ($array) {
            return $this->commitToArray($commit);
        }

        return $commit;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $commits = [];

        /** @var Commit $commit */
        foreach ($this->log->getCommits() as $commit) {
            $commits[] = $this->commitToArray($commit);
        }

        return $commits;
    }

    /**
     * @param $commit
     * @return array
     */
    public static function commitToArray(Commit $commit)
    {
        $branches = [];
        foreach ($commit->getIncludingBranches(true, false) as $branch) {
            $branches[] = $branch->getName();
        }

        return [
            'hash' => $commit->getHash(),
            'shortHash' => $commit->getShortHash(),
            'message' => $commit->getMessage(),
            'shortMessage' => $commit->getShortMessage(),
            'image' => "//www.gravatar.com/avatar/" . md5($commit->getAuthorEmail()),
            'branches' => $branches,
            'name'=>$commit->getAuthorName(),
            'email'=>$commit->getAuthorEmail(),
            'date'=>$commit->getCommitterDate()->format(DATE_ISO8601),
        ];
    }
}
