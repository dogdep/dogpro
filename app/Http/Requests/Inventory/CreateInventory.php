<?php namespace App\Http\Requests\Inventory;


/**
 * Class CreateInventory
 */
class CreateInventory extends UpdateInventory
{
    public function rules()
    {
        return parent::rules() + ['repo_id' => 'required|exists:repos,id'];
    }

    public function data()
    {
        return parent::data() + ['repo_id' => $this->get('repo_id')];
    }
}
