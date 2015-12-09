<?php namespace App\Model;

use App\Git\CommitPager;
use Gitonomy\Git\Exception\ProcessException;
use Gitonomy\Git\Repository;
use Illuminate\Support\Facades\Storage;

/**
 * Class Repo
 *
 * @property int $id
 * @property string $url
 * @property string $name
 * @property string $group
 * @property array $params
 * @property Inventory[] $inventories
 * @property User[]|\Illuminate\Database\Eloquent\Collection $users
 * @property Release[] releases
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|Repo whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Repo whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|Repo whereName($value)
 * @method static \Illuminate\Database\Query\Builder|Repo whereGroup($value)
 * @method static \Illuminate\Database\Query\Builder|Repo whereParams($value)
 * @method static \Illuminate\Database\Query\Builder|Repo whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Repo whereUpdatedAt($value)
 */
class Repo extends Model
{
    /**
     * @var CommitPager
     */
    private $commits;

    /**
     * @var array
     */
    protected $fillable = ['url', 'name', 'group'];

    /**
     * @var array
     */
    protected $hidden = ['releases'];

    /**
     * @var Repository
     */
    private $repo;

    public function checkHooks()
    {
        $hook = $this->repoPath("hooks");

        if (!is_link($hook)) {
            app('files')->deleteDirectory($hook);
            symlink(app_path("../scripts/hooks"), $hook);
        }
    }

    /**
     * @param $commit
     * @return \Gitonomy\Git\Commit
     */
    public function commit($commit)
    {
        return $this->git()->getCommit($commit);
    }

    /**
     * @return CommitPager
     */
    public function commits()
    {
        if (is_null($this->commits) && $this->git()) {
            $this->commits = new CommitPager($this->git());
        }

        return $this->commits;
    }

    /**
     * @return Repository
     */
    public function git()
    {
        if (is_dir($this->repoPath()) && is_null($this->repo)) {
            $this->repo = new Repository($this->repoPath(), config('git.options'));
        }

        return $this->repo;
    }

    /**
     * @return bool
     */
    public function isCloned()
    {
        try {
            return !is_null($this->git()) && !is_null($this->git()->run('log'));
        } catch (ProcessException $e) {
            return false;
        }
    }

    public function param($string, $default = null)
    {
        if (isset($this->params[$string])) {
            return $this->params[$string];
        }

        return $default;
    }

    /**
     * @param null|string $path
     * @return string
     */
    public function releasePath($path = null)
    {
        return storage_path(sprintf("releases/%s/%s", $this->group, $this->name)) . ($path ? "/$path" : "");
    }

    /**
     * @param null|string $path
     * @return string
     */
    public function repoPath($path = null)
    {
        return storage_path(sprintf("repos/%s/%s", $this->group, $this->name)) . ($path ? "/$path" : "");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + [
            'inventories' => $this->inventories,
            'users' => $this->users,
            'hookUrl'=> config('app.url') . action('HookController@pull', [$this->id], false)
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function releases()
    {
        return $this->hasMany(Release::class, "repo_id", "id")->orderBy('created_at', 'desc');
    }

    /**
     * @param string $value
     * @return array
     */
    public function getParamsAttribute($value)
    {
        if ($value) {
            $data = (array) json_decode($value, true);
            foreach (array_keys($data) as $key) {
                if (empty($data[$key])) {
                    unset($data[$key]);
                }
            }

            return $data;
        }

        return null;
    }

    /**
     * @param $value
     */
    public function setParamsAttribute($value)
    {
        if ($value) {
            $this->attributes['params'] = json_encode($value);
        }
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'repo_users');
    }

    public function canAccess(User $user)
    {
        if ($user->admin) {
            return true;
        }

        return $this->users->contains('id', $user->id);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('group', 'asc')->orderBy('name', 'asc');
    }
}
