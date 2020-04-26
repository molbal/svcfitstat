<?php

namespace App\Console\Commands;

use App\Http\Controllers\FitCacheController;
use Illuminate\Console\Command;

class MaintainLongTermCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sfs:prune_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prunes long term cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var FitCacheController $c */
        $c = resolve('App\Http\Controllers\FitCacheController');
        $c->pruneCache();
    }
}
