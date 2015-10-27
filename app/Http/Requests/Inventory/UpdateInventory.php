<?php namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateInventory
 */
class UpdateInventory extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'inventory' => 'required|string',
            'params' => 'array',
        ];
    }

    public function data()
    {
        return [
            'inventory' => $this->get('inventory'),
            'name' => $this->get('name'),
            'params' => $this->get('params'),
        ];
    }
}
