<?php namespace App\Http\Requests\Release;

use App\Http\Requests\Request;
use App\Model\Repo;
use App\Traits\FilterArray;

/**
 * Class CreateRelease
 * @package App\Http\Requests\Release
 */
class CreateRelease extends Request
{
    use FilterArray;

    public function rules()
    {
        return [
            'repo_id' => 'required|exists:repos,id',
            'inventory_id' => 'required|exists:inventories,id',
            'roles' => 'array|required|at_least:1'
        ];
    }

    public function roles()
    {
        return $this->filterArray($this->get('roles'));
    }

    /**
     * @return Repo
     */
    public function repo()
    {
        return Repo::query()->where('id', $this->get('repo_id'))->first();
    }
}
