<?php

    namespace App\Jobs;

    use App\WorkerConnector\WorkerConnector;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Log;

    class ProcessFitStat implements ShouldQueue {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        /**
         * The number of times the job may be attempted.
         *
         * @var int
         */
        public $tries = 3;

        /**
         * Determine the time at which the job should timeout.
         *
         * @return \DateTime
         */
        public function retryUntil()
        {
            return now()->addSeconds(15);
        }

        /** @var array */
        protected $params;

        /**
         * ProcessFitStat constructor.
         *
         * @param array $params
         */
        public function __construct(array $params) {
            $this->params = $params;
            echo "Constructing ProcessFitStat: ".print_r($params, 1);
        }


        /**
         * Execute the job.
         *
         * @param WorkerConnector $workerConnector
         *
         * @return void
         */
        public function handle(WorkerConnector $workerConnector) {
            try {
                $a = $workerConnector->calculateStats($this->params["fit"]);
                Log::info(print_r($a, 1));
            }
            catch (\Exception $e) {
                Log::warning("Could not process job (".print_r($this->params)."): " . $e);
                $this->job->fail($e);
            }
        }

    }
