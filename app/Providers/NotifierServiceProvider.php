<?php namespace App\Providers;

use App\Services\Notifiers\SlackNotifier;
use App\Services\NotifierService;
use Illuminate\Support\ServiceProvider;

/**
 * Class NotifierServiceProvider
 */
class NotifierServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->tag(SlackNotifier::class, NotifierService::NOTIFIER_TAG);
    }
}
