<?php namespace App\Http\Controllers;

use App\Exceptions\VaultException;
use App\Http\Requests\Inventory\CreateInventory;
use App\Http\Requests\Inventory\UpdateInventory;
use App\Model\Inventory;
use App\Model\Repo;
use App\Services\VaultService;
use Illuminate\Http\Request;

/**
 * Class InventoryController
 */
class InventoryController extends Controller
{
    /** @var VaultService  */
    private $vault;

    public function __construct(VaultService $vault)
    {
        $this->vault = $vault;
    }

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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(CreateInventory $request)
    {
        if ($request->get('download_keys')) {
            try {
                $this->vault->downloadHostKeys($request->get('inventory'));
            } catch (VaultException $e) {
                return response()->invalid($e->getMessage());
            }
        }

        $inv = Inventory::create([
            'repo_id' => $request->get('repo_id'),
            'inventory' => $request->get('inventory'),
            'name' => $request->get('name'),
            'params' => $request->get('params'),
        ]);

        return $inv;
    }

    /**
     * @param Repo $repo
     * @param Inventory $inv
     * @param UpdateInventory $request
     * @return Inventory
     */
    public function update(Repo $repo, Inventory $inv, UpdateInventory $request)
    {
        if ($request->get('download_keys')) {
            try {
                $this->vault->downloadHostKeys($request->get('inventory'));
            } catch (VaultException $e) {
                return response()->invalid($e->getMessage());
            }
        }

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
     * @return \Illuminate\Database\Eloquent\Collection|static[]
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
