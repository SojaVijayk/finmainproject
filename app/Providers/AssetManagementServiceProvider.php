<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\AssetCategory;
use App\Models\AssetStatus;

class AssetManagementServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Share categories and statuses with all asset views
        View::composer(['assets.*', 'asset-models.*', 'reports.*'], function ($view) {
            $view->with('categories', AssetCategory::all());
            $view->with('statuses', AssetStatus::all());
        });
    }

    public function register()
    {
        //
    }
}
