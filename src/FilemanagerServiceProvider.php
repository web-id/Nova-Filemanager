<?php

namespace WebId\Filemanager;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use WebId\Filemanager\App\Repositories\Contracts\MediaRepositoryContract;
use WebId\Filemanager\App\Repositories\MediaRepository;
use WebId\Filemanager\Http\Middleware\Authorize;
use App\Traits\ModuleServiceProviderTrait;
use WebId\Filemanager\App\Console\Commands\Install;
use WebId\Filemanager\App\Console\Commands\Uninstall;

class FilemanagerServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    protected $commands = [
        Install::class,
        Uninstall::class
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setPublishes(__DIR__);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-filemanager');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::script('filemanager-field', __DIR__.'/../dist/js/field.js');
            // Nova::style('filemanager-field', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/infinety-es/nova-filemanager')
            ->group(__DIR__.'/../routes/api.php');

        Route::middleware(['nova.ajax'])
            ->namespace('WebId\Filemanager\Http\Controllers')
            ->prefix('ajax/module/filemanager')
            ->group(__DIR__.'/../routes/ajax.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // register the artisan commands
        $this->commands($this->commands);

        $this->app->bind(
            MediaRepositoryContract::class,
            MediaRepository::class
        );
    }
}
