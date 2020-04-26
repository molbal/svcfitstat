<?php

    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $parser = new Parsedown();
    $readme = $parser->parse(file_get_contents(__DIR__."/../README.md"));
    return view('readme', ['readme' => $readme]);
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


    /**
     * Runs database migrations
     */
    Route::get("/maintenance/db/{secret}", function ($secret) {
        if ($secret != env("MAINTENANCE_TOKEN")) {
            abort(403, "Invalid maintenance token.");
        }
        echo "DB maintenance starts <br>";
        echo Artisan::call('migrate', ['--force' => true]);
        echo "DB maintenance Over";
    });

    Route::get("/maintenance/reset/{secret}", function ($secret) {
        Artisan::call("config:clear");
        Artisan::call("route:clear");
        Artisan::call("queue:restart");
    });
