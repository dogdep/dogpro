<?php namespace App\Http\Requests\Role;

use App\Ansible\Roles\RoleRepository;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateRole
 */
class CreateRole extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $roleConfig = app(RoleRepository::class)->all();
        $paramRules = [];

        $name = $this->get('name');
        if (isset($roleConfig['variables'][$name])) {
            foreach ($roleConfig['variables'][$name] as $param=>$paramConfig) {
                $rules = [$paramConfig["type"]];
                $paramRules["params.$param"] = implode("|", $rules);
            }
        }

        return [
            'repo_id' => 'required|exists:repos,id',
            'name' => 'required|string|ansible_role',
            'params' => 'array',
        ] + $paramRules;
    }
}
