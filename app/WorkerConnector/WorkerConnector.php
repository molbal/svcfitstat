<?php


	namespace App\WorkerConnector;


	use App\Exceptions\WorkerException;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class WorkerConnector {

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
            $secret = env("SFS_WORKER_SECRET");
            Log::debug(sprintf("Calculating stats using %s (timeout  %d, secret %s)", $url, $timeout, $secret));
	        try {
                $response = Http::timeout($timeout)->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])->get($url, http_build_query([
                    "fit" => urlencode($fit),
                    "secret" => $secret
                ]));

                if (!$response->ok()) {
                    throw new \RuntimeException(sprintf("Response code is %d body: %s", $response->status(), print_r($response->body(), 1)));
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
