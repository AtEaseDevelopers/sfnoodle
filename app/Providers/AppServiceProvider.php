<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        //
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $notificationService = app(NotificationService::class);
                $view->with([
                    'unreadCount' => $notificationService->getUnreadCountForUser(),
                    'notifications' => $notificationService->getLatestNotificationsForUser(null, 7)
                ]);
            } else {
                $view->with([
                    'unreadCount' => 0,
                    'notifications' => collect([])
                ]);
            }
        });
    }
}
