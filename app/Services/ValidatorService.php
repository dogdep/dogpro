<?php namespace App\Services;

use App\Ansible\Roles\RoleRepository;
use App\Traits\FilterArray;
use Gitonomy\Git\Admin;
use Illuminate\Validation\Validator;

class ValidatorService extends Validator
{
    use FilterArray;

    /**
     * @param string $attribute
     * @param string $value
     * @return bool
     */
    public function validateGitUrl($attribute, $value)
    {
        return Admin::isValidRepository($value);
    }

    /**
     * @param string $attribute
     * @param string $value
     * @return bool
     */
    public function validateAnsibleRole($attribute, $value)
    {
        return array_key_exists($value, $this->getRoles()->all());
    }

    /**
     * @param string $attribute
     * @param array $value
     * @param array $parameters
     * @return bool
     */
    public function validateAtLeast($attribute, $value, $parameters)
    {
        $count = 0;
        foreach ($this->filterArray($value) as $val) {
            if (!empty($val)) {
                $count++;
            }
        }

        return $count >= $parameters[0];
    }

    /**
     * @return RoleRepository
     */
    private function getRoles()
    {
        return app(RoleRepository::class);
    }
}
