<?php namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateInventory
 */
class CreateInventory extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'repo_id' => 'required|exists:repos,id',
            'name' => 'required|string',
            'inventory' => 'required|string',
            'params' => 'array',
        ];
    }
}
