<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResetQuota extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sfs:reset_quota';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets quota. To be run monthly';

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
        $apps = DB::table("applications");
        foreach ($apps as $app) {
            DB::beginTransaction();
            $quotaMax = $app->QUOTA;
            $quotaUsed = DB::table("rolling")->where("APP_ID")->count();
            $quotaLeft = max(0, ($quotaMax-$quotaUsed));

            DB::table("historic")
                ->insert([
                    "APP_ID" => $app->APP_ID,
                    "YEAR" => now()->year,
                    "MONTH" => now()->month,
                    "SAVED" => $quotaMax,
                    "USED" => $quotaUsed,
                    "LEFT" => $quotaLeft
                ]);

            DB::table("rolling")->where("APP_ID")->delete();
            DB::commit();
        }

        if (DB::table("rolling")->count() > 0) {
            Log::error("Error: Rolling table still has contents after clearing them. Purging.");
            DB::table("rolling")->truncate();
        }



        Log::info("Resets OK");
    }
}
