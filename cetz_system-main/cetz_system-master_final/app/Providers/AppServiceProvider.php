<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Institution;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;


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
   public function boot()
{
    // تمرير بيانات المؤسسة لجميع الViews
  View::composer('*', function ($view) {
    $institute = Cache::remember('institute_data', 3600, function () {
        return Institution::first();
    });
    $view->with('institute', $institute);
});
}
}
