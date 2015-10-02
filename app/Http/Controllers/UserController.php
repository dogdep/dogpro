<?php namespace App\Http\Controllers;

use App\Model\User;

/**
 * Class UserController
 */
class UserController extends Controller
{
    public function all()
    {
        return User::all();
    }
}
