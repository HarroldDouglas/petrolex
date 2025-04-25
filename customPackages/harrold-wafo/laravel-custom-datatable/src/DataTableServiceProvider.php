<?php

namespace HarroldWafo\LaravelCustomDatatable;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DataTableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // Register routes, views, etc.
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'harroldwafo-laravel-datatable');

        $this->publishRappasoft();
        $this->registerPublishableResources();
        $this->mergeTranslations();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/livewire-tables.php',
            'livewire-tables'
        );
    }

    /**
     * Publish Rappasoft's config if needed
     */
    private function publishRappasoft()
    {
        if (class_exists('Rappasoft\\LaravelLivewireTables\\LaravelLivewireTablesServiceProvider') &&
            !file_exists(config_path('livewire-tables.php'))) {

            Artisan::call('vendor:publish', [
                '--provider' => 'Rappasoft\\LaravelLivewireTables\\LaravelLivewireTablesServiceProvider',
                '--tag' => 'livewire-tables-config'
            ]);
        }
    }

    /**
     * Register all publishable resources
     */
    private function registerPublishableResources()
    {
        // Group publishing - everything
        $this->publishes([
            __DIR__ . '/../config/livewire-tables.php' => config_path('livewire-tables.php'),
            __DIR__ . '/../resources/views/partials' => resource_path('views/vendor/harroldwafo-laravel-datatable/partials'),
            __DIR__ . '/../resources/lang' => lang_path(),
        ], 'harroldwafo-laravel-datatable-all');

        // Individual publishing
        $this->publishes([
            __DIR__ . '/../config/livewire-tables.php' => config_path('livewire-tables.php'),
        ], 'livewire-tables-config');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path(),
        ], 'harroldwafo-laravel-datatable-translations');

        // Publish CSS theme files
        $this->publishes([
            __DIR__ . '/../public/css/axelit' => public_path('vendor/harroldwafo-laravel-datatable/css/axelit'),
        ], 'harroldwafo-laravel-datatable-theme-axelit');

        // Publish Blade templates
        $this->publishes([
            __DIR__ . '/../resources/views/partials' => resource_path('views/vendor/harroldwafo-laravel-datatable/partials'),
        ], 'harroldwafo-laravel-datatable-views');
    }

    /**
     * Merge translations to preserve existing ones
     */
    private function mergeTranslations()
    {
        $packageTranslationsPath = __DIR__ . '/../resources/lang/fr.json';
        $appTranslationsPath = lang_path('fr.json');

        if (!file_exists($packageTranslationsPath)) {
            return;
        }

        $packageTranslations = json_decode(file_get_contents($packageTranslationsPath), true) ?? [];

        if (file_exists($appTranslationsPath)) {
            $appTranslations = json_decode(file_get_contents($appTranslationsPath), true) ?? [];
            $mergedTranslations = array_merge($packageTranslations, $appTranslations);

            if (!empty($mergedTranslations)) {
                File::put(
                    $appTranslationsPath,
                    json_encode($mergedTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            }
        } else {
            if (!is_dir(dirname($appTranslationsPath))) {
                mkdir(dirname($appTranslationsPath), 0755, true);
            }

            File::put(
                $appTranslationsPath,
                json_encode($packageTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
    }
}
