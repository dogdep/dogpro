<?php namespace App\Http\Controllers;

use App\Http\Requests\Inventory\CreateInventory;
use App\Http\Requests\Inventory\UpdateInventory;
use App\Model\Inventory;
use App\Model\Repo;
use Illuminate\Http\Request;

/**
 * Class InventoryController
 */
class InventoryController extends Controller
{
    /**
     * @param Repo $repo
     * @param Inventory $inv
     * @throws \Exception
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Repo $repo, Inventory $inv)
    {
        $inv->delete();

        return $inv;
    }

    /**
     * @param CreateInventory $request
     * @return Inventory
     */
    public function create(CreateInventory $request)
    {
        return Inventory::create([
            'repo_id' => $request->get('repo_id'),
            'inventory' => $request->get('inventory'),
            'name' => $request->get('name'),
            'params' => $request->get('params'),
        ]);
    }

    /**
     * @param Repo $repo
     * @param Inventory $inv
     * @param UpdateInventory $request
     * @return Inventory
     */
    public function update(Repo $repo, Inventory $inv, UpdateInventory $request)
    {
        $inv->update([
            'repo_id' => $request->get('repo_id'),
            'inventory' => $request->get('inventory'),
            'name' => $request->get('name'),
            'params' => $request->get('params'),
        ]);

        return $inv;
    }

    /**
     * @param Request $request
     * @return Inventory[]
     */
    public function index(Request $request)
    {
        $inventories = Inventory::query();
        if ($request->has('repo_id')) {
            $inventories->where("repo_id", $request->get('repo_id'));
        }

        return $inventories->get();
    }
}
