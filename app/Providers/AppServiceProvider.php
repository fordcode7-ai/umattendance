<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
/**
* Register any application services.
*/
public function register(): void
{
//
<<<<<<< HEAD
}
/**
* Bootstrap any application services.
*/
public function boot(): void
{
if (env('APP_ENV') === 'production') {
URL::forceScheme('https');
}
}
=======
>>>>>>> 3adb323 (Updated code)
}
/**
* Bootstrap any application services.
*/
public function boot(): void
{
if (env('APP_ENV') === 'production') {
URL::forceScheme('https');
}
}
}