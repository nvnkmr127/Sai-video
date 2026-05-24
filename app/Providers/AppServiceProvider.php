<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        try {
            if (\Schema::hasTable('settings')) {
                $siteSettings = [
                    'logo' => \App\Models\Setting::getValue('logo'),
                    'footer_text' => \App\Models\Setting::getValue('footer_text', '© ' . date('Y') . ' WorkshopPro. All rights reserved.'),
                    'slider_images' => json_decode(\App\Models\Setting::getValue('slider_images', '[]'), true),
                    'success_background' => \App\Models\Setting::getValue('success_background'),
                ];
                view()->share('siteSettings', $siteSettings);
            }
        } catch (\Exception $e) {
            // Silently fail if DB not ready
        }
    }
}
