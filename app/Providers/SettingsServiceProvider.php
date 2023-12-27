<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\QueryException;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            // Check if the Settings model exists and the table is migrated
            if ($this->app->runningInConsole() || $this->settingsTableExists()) {
                $settings = Settings::pluck('value', 'key')->toArray();
                Config::set('settings', $settings);
            }
        } catch (QueryException $e) {
            // Log or handle the error if needed
            // For example, you might log the exception message or handle it gracefully
            // Log::error($e->getMessage());
        }
    }

    /**
     * Check if the settings table exists.
     *
     * @return bool
     */
    private function settingsTableExists(): bool
    {
        return \Schema::hasTable((new Settings())->getTable());
    }
}
