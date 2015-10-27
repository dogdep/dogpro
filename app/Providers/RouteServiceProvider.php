<?php

namespace App\Providers;

use App\Model\Inventory;
use App\Model\Release;
use App\Model\Repo;
use App\Model\SshKey;
use App\Model\User;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);

        $router->model('user', User::class);
        $router->model('repo', Repo::class);
        $router->model('release', Release::class);
        $router->model('inventory', Inventory::class);
        $router->bind('key', function($key) {
            return SshKey::get($key) ?: abort(404);
        });
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function($router) {
            require app_path('Http/routes.php');
        });
    }
}
