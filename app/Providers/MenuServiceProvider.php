<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
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
    // Use View Composer to allow Auth check
    View::composer('*', function ($view) {
      static $menuData = null;
      if ($menuData === null) {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isEmployee = $user && $user->role !== 'admin';

        $menuPath = $isEmployee
          ? 'resources/menu/verticalMenuEmployee.json'
          : 'resources/menu/verticalMenu.json';

        // Fallback if file doesn't exist
        if ($isEmployee && !file_exists(base_path($menuPath))) {
          $menuPath = 'resources/menu/verticalMenu.json';
        }

        $verticalMenuJson = file_get_contents(base_path($menuPath));
        $verticalMenuData = json_decode($verticalMenuJson);

        $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
        $horizontalMenuData = json_decode($horizontalMenuJson);

        $menuData = [$verticalMenuData, $horizontalMenuData];
      }

      $view->with('menuData', $menuData);
    });
  }
}
