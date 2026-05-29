<?php

namespace App\Providers;

use App\Models\Item;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        // Explicit route model binding: route param '{inventory}' vs controller param '$item'
        Route::bind('inventory', fn (string $value): Item => Item::findOrFail($value));
    }
}
