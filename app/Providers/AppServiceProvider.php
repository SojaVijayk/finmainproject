<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

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
    //
    // Paginator::useBootstrapFive();
  //  $user = auth()->user();

  //   View::share('pageConfigs', [
  //       'myLayout' => match (true) {
  //           $user && $user->user_role == 3 => 'blank',
  //           $user && $user->user_role == 1 => 'vertical',
  //           $user && $user->user_role == 2 => 'horizontal',
  //           default => 'horizontal',
  //       }
  //   ]);
  }
}
