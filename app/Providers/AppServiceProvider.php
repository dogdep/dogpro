<?php namespace App\Providers;

use App\Services\GitlabProvider;
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
        $this->app->bind('Pusher', function() {
            return new \Pusher(
                config('services.pusher.key'),
                config('services.pusher.secret'),
                config('services.pusher.id'),
                ['encrypted' => true]
            );
        });
    }
}
