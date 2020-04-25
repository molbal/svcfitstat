<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
//        Queue::before(function (JobProcessing $event) {
//            // $event->connectionName
//            // $event->job
//            // $event->job->payload()
//            echo "before: conname:".$event->connectionName." jobname:".$event->job->getName()." pl:".print_r($event->job->payload(), true);
//            Log::info("before: conname:".$event->connectionName." jobname:".$event->job->getName()." pl:".print_r($event->job->payload(), true));
//
//        });
//
//        Queue::after(function (JobProcessed $event) {
//            // $event->connectionName
//            // $event->job
//            // $event->job->payload()
//            echo "after: conname:".$event->connectionName." jobname:".$event->job->getName()." pl:".print_r($event->job->payload(), true);
//            Log::info("after: conname:".$event->connectionName." jobname:".$event->job->getName()." pl:".print_r($event->job->payload(), true));
//
//        });
    }
}
