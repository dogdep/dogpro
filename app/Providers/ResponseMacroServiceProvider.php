<?php namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Response;
use Illuminate\Support\ServiceProvider;

/**
 * Class ResponseMacroServiceProvider
 */
class ResponseMacroServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Response::macro('invalid', function($message)
        {
            return new JsonResponse([
                'error'=>[$message]
            ], 422);
        });
    }
}
