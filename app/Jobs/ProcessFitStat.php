<?php

    namespace App\Jobs;

    use App\Http\Controllers\CallbackController;
    use App\Http\Controllers\FitCacheController;
    use App\WorkerConnector\WorkerConnector;
    use Exception;
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
        public $tries = 2;

        public function retryAfter() {
            return now()->addSeconds(1800);

        }

        /**
         * Determine the time at which the job should timeout.
         *
         * @return \DateTime
         */
        public function retryUntil()
        {
            return now()->addSeconds(1800);
        }

        /** @var array */
        protected $params;

        /**
         * ProcessFitStat constructor.
         *
         * @param array $params
         */
        public function __construct(array $params) {
            Log::info("Constructing job: ".print_r($params ,1));
            $this->params = $params;
        }

        /**
         * The job failed to process.
         *
         * @param  Exception  $exception
         * @return void
         */
        public function failed(Exception $exception)
        {
            Log::error("Error in ProcessFitStat: ".get_class($exception). " ".$exception->getMessage(). "\n".$exception->getFile()."@".$exception->getLine());
        }
        /**
         * Execute the job.
         *
         * @param WorkerConnector    $workerConnector
         * @param CallbackController $callbackController
         * @param FitCacheController $fitCacheController
         *
         * @return void
         */
        public function handle(WorkerConnector $workerConnector, CallbackController $callbackController, FitCacheController $fitCacheController) {
            Log::debug("Queue worker starting: ".print_r($this->params, 1));
            try {
                $fitValue = $fitCacheController->getCacheValue($this->params["fit"]);
                if (!$fitValue) {
                    $fitValue = $workerConnector->calculateStats($this->params["fit"]);
                    $fitCacheController->putCache($this->params["fit"], $fitValue);
                }

                $callbackController->doCallback(
                    $this->params["callback"],
                    $this->params["externalId"],
                    $this->params["appId"],
                    $fitValue);
            }
            catch (\Exception $e) {
                Log::warning("Could not process job (".print_r($this->params, 1)."): " . $e);
                $this->fail($e);
            }
        }

    }
