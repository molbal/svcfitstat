<?php

    namespace App\Jobs;

    use FitStatRequest;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;

    class ProcessFitStat implements ShouldQueue {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        /** @var FitStatRequest */
        protected $fitStatRequest;

        /**
         * Create a new job instance.
         *
         * @param FitStatRequest $fitStatRequest
         */
        public function __construct(FitStatRequest $fitStatRequest) {
            $this->fitStatRequest = $fitStatRequest;
    }

        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle() {
            //
        }
    }
