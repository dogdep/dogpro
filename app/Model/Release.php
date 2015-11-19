<?php namespace App\Model;

use App\Config\DogproConfig;
use App\Git\CommitPager;
use Gitonomy\Git\Commit;

/**
 * Class Release
 *
 * @property int $id
 * @property int $repo_id
 * @property array|null $roles
 * @property array|null $params
 * @property string $commit
 * @property string $status
 * @property Inventory inventory
 * @property Repo $repo
 * @property string $raw_log
 * @property integer $inventory_id
 * @property integer $user_id
 * @property integer $time
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|Release whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereCommit($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereRoles($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereRawLog($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereInventoryId($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereRepoId($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereTime($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereStartedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Release whereUpdatedAt($value)
 */
class Release extends Model
{
    const PLAYBOOK_FILENAME = "_dogpro_run.yml";
    const INVENTORY_FILENAME = "_dogpro_inventory";

    const QUEUED = 'queued';
    const PREPARING = 'preparing';
    const ERROR = 'error';
    const RUNNING = 'running';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';

    /**
     * @var DogproConfig
     */
    private $config;

    /**
     * @var array
     */
    protected $fillable = [
        'repo_id',
        'status',
        'inventory_id',
        'commit',
        'user_id',
        'roles',
        'raw_log',
        'params',
        'time',
        'started_at'
    ];

    /**
     * @return float
     */
    public function avg()
    {
        return (float) $this->query()
            ->where('repo_id', $this->repo_id)
            ->where('roles', $this->attributes['roles'])
            ->where('status', Release::COMPLETED)
            ->where('time', '>', 0)
            ->avg('time');
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        $this->status = $this->query()->where('id', $this->id)->value('status');
        return $this->status == self::CANCELLED;
    }

    /**
     * @return array
     */
    public function getDates()
    {
        return array_merge(parent::getDates(), ['started_at']);
    }

    /**
     * @return Commit
     */
    public function commit()
    {
        if ($this->repo->git()) {
            return $this->repo->git()->getCommit($this->commit);
        }

        return null;
    }

    /**
     * @return DogproConfig
     */
    public function config()
    {
        if (is_null($this->config)) {
            if (is_file($this->path(DogproConfig::FILENAME))) {
                $this->config = new DogproConfig(file_get_contents($this->path(DogproConfig::FILENAME)));
                return $this->config;
            } else {
                $this->config = new DogproConfig();
            }
        }
        return $this->config;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * @return bool
     */
    public function isCancellable()
    {
        return in_array($this->status, [self::QUEUED, self::RUNNING, self::PREPARING]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }

    /**
     * @param string|null $path
     * @return string
     */
    public function path($path = null)
    {
        return $this->repo->releasePath("{$this->id}_{$this->commit}") . ($path ? "/$path" : null);
    }

    /**
     * @return string
     */
    public function url()
    {
        return sprintf('%s/repo/%d/releases/%d', env('APP_URL'), $this->repo->id, $this->id);
    }

    /**
     * @param string $value
     * @return array
     */
    public function getRolesAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true);
    }

    /**
     * @param string $key
     * @return null
     */
    public function param($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * @param array $value
     */
    public function setRolesAttribute(array $value)
    {
        $this->attributes['roles'] = json_encode(array_values($value));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + [
            'commit_info' => $this->commit() ? CommitPager::commitToArray($this->commit()) : null,
        ];
    }

    /**
     * @param string $value
     * @return integer
     */
    public function getStartedAtAttribute($value)
    {
        return empty($value) ? null : strtotime($value);
    }

    public function getParamsAttribute($value)
    {
        if (!empty($value)) {
            return json_decode($value, true);
        }

        return [];
    }

    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = json_encode((array) $value);
    }
}
