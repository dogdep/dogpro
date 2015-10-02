<?php namespace App\Http\Controllers;

use App\Jobs\Ssh\RegenerateSshConfig;
use App\Model\SshKey;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Http\Request;

/**
 * Class KeysController
 */
class KeysController extends Controller
{
    use DispatchesCommands;

    public function index()
    {
        return SshKey::all();
    }

    public function delete(SshKey $key)
    {
        $key->delete();
        $this->regenerateConfig();

        return $key;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        $name = $request->get('id');
        $i = 1;
        while(SshKey::get($name)) {
            $name = $request->get('id') . "-" . $i;
        }

        $key = new SshKey($name, $request->get('content'));
        $key->save();

        $this->regenerateConfig();

        return $key;
    }

    private function regenerateConfig()
    {
        $this->dispatch(new RegenerateSshConfig());
    }
}
