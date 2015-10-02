<?php

namespace App\Providers;

use App\Services\GitlabProvider;
use App\Services\VaultService;
use GitlabAuth\Auth;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Socialite::extend('gitlab', function() {
            return \Socialite::buildProvider(GitlabProvider::class, config('services.gitlab'));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Services\VaultService', function () {
            $client = new Client([
                'base_url' => config('services.vault.url'),
                'defaults' => [
                    'verify' => false,
                    'auth' => [config('services.vault.id'), config('services.vault.secret')]
                ]
            ]);

            return new VaultService(
                $client,
                \Storage::disk('server_keys')
            );
        });

        $this->app->bind('Pusher', function () {
            return new \Pusher(
                config('services.pusher.key'),
                config('services.pusher.secret'),
                config('services.pusher.id'),
                ['encrypted' => true]
            );
        });
    }
}
