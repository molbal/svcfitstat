<?php


	namespace App\WorkerConnector;


	use App\Exceptions\WorkerException;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class WorkerConnector {

//        /**
//         * Restarts local worker
//         */
//        public function restartWorker() {
//
//            Log::info("Restarting container.");
//            $cmds = [];
//            // Stop
//            $cmds[] = "docker kill svcfitstat";
//
//            // Clean
//             $cmds[] = "docker rm svcfitstat";
//
//            // Start
//             $cmds[] = "docker run -p 8002:80 --name svcfitstat molbal/svcfitstat:0.9.2 &";
//
//            foreach ($cmds as $cmd) {
//                Log::debug("Executing $cmd -> ".shell_exec($cmd));
//             }
//        }


        /**
         * Calls the docker worker
         * @param string $fit
         *
         * @return array
         * @throws WorkerException When worker returns an issue: Possible input data fault
         * @throws ConnectionException When the worker timeouts: Possible container fault
         */
	    public function calculateStats(string $fit) {
            $url = env("SFS_WORKER_URL");
            $timeout = env("SFS_WORKER_TIMEOUT", 20);
            Log::debug(sprintf("Calculating stats using %s (timeout  %d)", $url, $timeout));
	        try {
                $response = Http::timeout($timeout)->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])->post($url, [
                    "fit" => $fit,
                    "secret" => env("SFS_WORKER_SECRET")]);

                if (!$response->ok()) {
                    throw new \RuntimeException("Response code is ", $response->status(). " body:".print_r($response->body()));
                }

                $json = $response->json();
                if (!$json["success"]) {
                    throw new WorkerException("Error: ".print_r($json, 1),$response->status());
                }

                return $json["stats"];
            }
            catch (ConnectionException $exc) {
	            Log::error("Could not connect to the worker at $url with $timeout seconds timeout.");
	            throw new ConnectionException("Worker returned an empty error", -1000);
	        }
	        catch (\Exception $exc) {
	            Log::error("Unknown exception: ".$exc);
                throw new WorkerException("Worker returned an empty error", -1000);
            }
        }

	}
