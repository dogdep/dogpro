<?php namespace App\Http\Requests\Repo;

use App\Http\Requests\Request;

/**
 * Class CreateRepoRequest
 */
class CreateRepoRequest extends Request
{
    public function rules()
    {
        return [
            'url' => 'required|git_url',
            'name' => 'required|max:255',
            'group' => 'string|max:255',
        ];
    }
}
