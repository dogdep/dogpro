<?php namespace App\Http\Requests\Release;
use App\Http\Requests\Request;
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
            'roles'=>'array|required|at_least:1'
        ];
    }

    public function roles()
    {
        return $this->filterArray($this->get('roles'));
    }
}
