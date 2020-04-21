<?php


	namespace App\WorkerConnector;


	use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class WorkerConnector {

        public function restartWorker() {

            Log::info("Restarting container.");
            $cmds = [];
            // Stop
            $cmds[] = "docker kill svcfitstat";

            // Clean
             $cmds[] = "docker rm svcfitstat";

            // Start
             $cmds[] = "docker run -p 82:80 --name svcfitstat molbal/svcfitstat:0.9.1 &";

            foreach ($cmds as $cmd) {
                Log::debug("Executing $cmd -> ".shell_exec($cmd));
             }
        }

	    public function calculateStats(string $fit) {
            $url = env("SFS_WORKER_URL");
            $timeout = env("SFS_WORKER_TIMEOUT", 20);
            Log::debug(sprintf("Calculating stats using %s (timeout  %d)", $url, $timeout));
	        try {
                $response = Http::timeout($timeout)->get($url, ["fit" => urlencode($fit)]);

                if (!$response->ok()) {
                    throw new \RuntimeException("Response code is ", $response->status(). " body:".print_r($response->body()));
                }

                return $response->json();
            }
            catch (ConnectionException $exc) {
	            Log::error("Could not connect to the worker at $url with $timeout seconds timeout");
	        }
        }

	}
