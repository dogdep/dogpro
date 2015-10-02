<?php namespace App\Model;

use App\Ansible\Config\PlaybookConfig;
use App\Config\DogproConfig;
use App\Exceptions\ReleaseException;
use App\Git\CommitPager;
use Gitonomy\Git\Commit;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Release
 *
 * @property int $id
 * @property int $repo_id
 * @property array|null $roles
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
 * @property-read Inventory $inventory
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereCommit($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereRoles($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereRawLog($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereInventoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereRepoId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereTime($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereStartedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Release whereUpdatedAt($value)
 */
class Release extends Model
{
    const LOG_FORMAT = "[%datetime%]: %message% %context% %extra%\n";

    const PLAYBOOK_FILENAME = "_dogpro_run.yml";
    const INVENTORY_FILENAME = "_dogpro_inventory";
    const REVISION_FILENAME = "_dogpro_revision";
    const LOG_RAW_FILENAME = "_dogpro_raw.log";
    const LOG_FILENAME = "_dogpro_play.log";
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
        'time',
        'started_at'
    ];

    /**
     * @var Logger
     */
    private static $log = [];

    public function avg()
    {
        return \DB::table('releases')
            ->where('repo_id', $this->repo_id)
            ->where('roles', $this->attributes['roles'])
            ->where('status', Release::COMPLETED)
            ->where('time', '>', 0)
            ->avg('time');
    }

    public function getDates()
    {
        return array_merge(parent::getDates(), ['started_at']);
    }


    /**
     * @return Commit
     */
    public function commit()
    {
        return $this->repo->git()->getCommit($this->commit);
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

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function isCancellable()
    {
        return in_array($this->status, [self::QUEUED, self::RUNNING, self::PREPARING]);
    }

    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }

    /**
     * @return string
     */
    public function playbookFilename()
    {
        return self::PLAYBOOK_FILENAME;
    }

    /**
     * @return string
     */
    public function inventoryFilename()
    {
        return self::INVENTORY_FILENAME;
    }

    /**
     * Initialize (ignore any error)
     * @return Release
     */
    public function init()
    {
        $path = $this->path();
        @mkdir($path, 0777, true);
        @unlink($this->path(self::LOG_RAW_FILENAME));
        @unlink($this->path(self::LOG_FILENAME));

        return $this;
    }

    public function getAnsibleRawLog()
    {
        $path = $this->path(self::LOG_FILENAME);
        if (is_file($path) && is_readable($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    public function message($type, $message)
    {
        @file_put_contents($this->path(self::LOG_FILENAME), json_encode([
            'event' => 'message',
            'type' => $type,
            'msg' => $message,
        ]), FILE_APPEND);
    }

    /**
     * @return Logger
     */
    public function logger()
    {
        if (!isset(self::$log[$this->name()])) {
            $handler = new StreamHandler($this->path(self::LOG_RAW_FILENAME));
            $handler->setFormatter(new LineFormatter(self::LOG_FORMAT, null, false, true));

            self::$log[$this->name()] = new Logger("releaseLog", [$handler]);
        }

        return self::$log[$this->name()];
    }

    /**
     * @param string|null $path
     * @return string
     */
    public function path($path = null)
    {
        return $this->repo->releasePath("{$this->id}_{$this->commit}") . ($path ? "/$path" : null);
    }

    public function resetLogs()
    {
        @file_put_contents($this->path(self::LOG_FILENAME), "");
        @file_put_contents($this->path(self::LOG_RAW_FILENAME), "");
    }


    /**
     * @return string
     */
    public function url()
    {
        return sprintf('%s/repo/%d/releases/%d', env('APP_URL'), $this->repo->id, $this->id);
    }

    /**
     * @param PlaybookConfig $playbook
     * @param Inventory $inventory
     * @param Commit $commit
     * @throws ReleaseException
     */
    public function write(PlaybookConfig $playbook, Inventory $inventory, Commit $commit)
    {
        if (!@file_put_contents($this->path(self::PLAYBOOK_FILENAME), $playbook->render())) {
            throw new ReleaseException($this, "Cannot write playbook file!");
        }

        if (!@file_put_contents($this->path(self::INVENTORY_FILENAME), $inventory->render())) {
            throw new ReleaseException($this, "Cannot write inventory file!");
        }

        if (!@file_put_contents($this->path(self::REVISION_FILENAME), $commit->getHash())) {
            throw new ReleaseException($this, "Cannot revision file!");
        }
    }

    /**
     * @return string
     */
    private function name()
    {
        return sprintf("%s_%s", $this->repo->name, $this->commit);
    }

    public function getRolesAttribute()
    {
        if (empty($this->attributes['roles'])) {
            return [];
        }

        return json_decode($this->attributes['roles'], true);
    }

    public function setRolesAttribute(array $value)
    {
        $this->attributes['roles'] = json_encode(array_values($value));
    }

    public function toArray()
    {
        return parent::toArray() + [
            'commit_info' => CommitPager::commitToArray($this->commit()),
        ];
    }

    public function getStartedAtAttribute()
    {
        return empty($this->attributes['started_at']) ? null : strtotime($this->attributes['started_at']);
    }
}
