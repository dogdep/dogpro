<?php

namespace App\Http\Controllers;

use App\Model\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * @return User|null
     */
    public function user()
    {
        $user = auth()->user();
        return $user instanceof User ? $user : null;
    }
}
