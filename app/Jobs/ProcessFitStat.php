<?php

    namespace App\Jobs;

    use App\WorkerConnector\WorkerConnector;
    use App\DTO\FitStatRequest;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Log;

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
            echo "1";
            Log::info("1");
            try {
                echo "2";
                Log::info("2");
                /** @var WorkerConnector $worker */
                $worker = resolve('App\WorkerConnector\WorkerConnector');
                echo "3";
                Log::info("3");
                print_r($worker->calculateStats($this->fitStatRequest->eft));

            }
            catch (\Exception $e) {
                echo "Could not process job (".print_r($this->fitStatRequest)."):  $e \n";
                Log::warning("Could not process job (".print_r($this->fitStatRequest)."): " . $e);
            }
        }
    }
