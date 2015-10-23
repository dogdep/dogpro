<?php namespace App\Http\Controllers;

use App\Git\SSH;
use App\Traits\ManageFilesystem;

/**
 * Class ConfigController
 */
class ConfigController extends Controller
{
    use ManageFilesystem;

    public function get()
    {
        $publicKeyPath = config('git.public_key');
        if (!is_file($publicKeyPath)) {
            SSH::writeKeyPair(config('dogpro'), config('git.public_key'), config('git.private_key'));
        }
        $public = file_get_contents($publicKeyPath);

        return ['public_key' => $public];
    }
}
