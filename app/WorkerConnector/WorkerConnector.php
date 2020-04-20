<?php


	namespace App\WorkerConnector;


	use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class WorkerConnector {

	    public function calculateStats(string $fit) {
            $url = env("SFS_WORKER_URL");
            $timeout = env("SFS_WORKER_TIMEOUT", 20);
	        try {
//                $response = Http::timeout($timeout)->post($url, ["fit" => $fit]);
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