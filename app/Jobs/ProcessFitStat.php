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
                Log::debug("TRACE 1");
                $fitValue = $fitCacheController->getCacheValue($this->params["fit"]);
                Log::debug("TRACE 2");
                if (!$fitValue) {
                    Log::debug("TRACE 3");
                    $fitValue = $workerConnector->calculateStats($this->params["fit"]);
                    Log::debug("TRACE 4");
                    $fitCacheController->putCache($this->params["fit"], $fitValue);
                    Log::debug("TRACE 5");
                }
                Log::debug("TRACE 6");

                Log::debug("TRACE 7");
                $callbackController->doCallback(
                    $this->params["callback"],
                    $this->params["externalId"],
                    $this->params["appId"],
                    $fitValue);
                Log::debug("TRACE 8");
            }
            catch (\Exception $e) {
                Log::warning("Could not process job (".print_r($this->params, 1)."): " . $e);
                $this->fail($e);
            }
        }

    }
