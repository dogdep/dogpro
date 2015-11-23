<?php namespace App\Model;

use App\Git\SSH;
use phpseclib\Crypt\RSA;

/**
 * Class Inventory
 *
 * @property int $id
 * @property int $repo_id
 * @property string $name
 * @property array $params
 * @property string $inventory
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Repo $repo
 * @property  string $public_key
 * @property  string $private_key
 * @method static Inventory[] all()
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Inventory whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Inventory whereRepoId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Inventory whereInventory($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Inventory whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Inventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Inventory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Model\Inventory whereParams($value)
 */
class Inventory extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['inventory', 'name', 'repo_id', 'params', 'private_key', 'public_key'];

    /**
     * @var array
     */
    protected $hidden = ['private_key'];

    /**
     * Use this in we'll need to modify contents
     *
     * @return string
     */
    public function render()
    {
        return (string) $this->inventory;
    }

    public function save(array $options = [])
    {
        if (empty($this->public_key) || empty($this->private_key)) {
            $this->generateKeys();
        }

        return parent::save($options);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }

    /**
     * @param string $value
     * @return array
     */
    public function getParamsAttribute($value)
    {
        if (!empty($value)) {
            return (array) json_decode($value, true);
        }

        return [];
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

    protected function generateKeys()
    {
        $key = SSH::generateKeyPair(sprintf("%s@dogpro", $this->name));
        $this->public_key = $key['publickey'];
        $this->private_key = $key['privatekey'];
    }

    public function toArray()
    {
        return parent::toArray() + [
            "remote" => sprintf("%s/git/%s/%s", env('APP_URL'), $this->repo->id, $this->name),
        ];
    }
}
