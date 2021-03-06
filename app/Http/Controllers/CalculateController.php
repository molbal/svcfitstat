<?php

    namespace App\Http\Controllers;

    use App\Http\Controllers\Auth\AppAuthController;
    use App\Jobs\ProcessFitStat;
    use App\Mail\ErrorMessage;
    use App\WorkerConnector\WorkerConnector;
    use http\Exception\RuntimeException;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Mail;

    class CalculateController extends Controller {

        /** @var AppAuthController */
        protected $auth;

        /** @var WorkerConnector */
        protected $worker;

        /** @var CallbackController */
        protected $callback;

        /**
         * CalculateController constructor.
         *
         * @param AppAuthController  $auth
         * @param WorkerConnector    $worker
         * @param CallbackController $callback
         */
        public function __construct(AppAuthController $auth, WorkerConnector $worker, CallbackController $callback) {
            $this->auth = $auth;
            $this->worker = $worker;
            $this->callback = $callback;
        }


        /**
         * Handles async calculation request
         *
         * @param Request $request
         *
         * @return array
         */
        public function handleAsync(Request $request) {
            [$fit, $appId, $appSecret, $fitId] = $this->extractInputFromRequest($request);

            try {
                $this->validateRequest($fit, $appId, $appSecret, $fitId);
                $this->auth->fileNewRequest($appId, $appSecret);

                ProcessFitStat::dispatch([
                    'fit' => $fit,
                    'externalId' => $fitId,
                    'appId' => $appId,
                    'callback' => $this->auth->getCallbackUrl($appId)
                ]);
                return ['success' => true, 'message' => "Fit calculation job dispatched to the task queue.", 'fit' => $fit,'id' => $fitId, 'expect_callback' => $this->auth->getCallbackUrl($appId)];
            }
            catch (\Exception $exc) {
                if (get_class($exc) == 'App\Exceptions\QuotaLimitException') {$this->sendErrorEmail($appId);                }
                return ['success' => false, 'message' => $exc->getMessage(), 'dispute' => "If you think this was caused by an error on our end please open an issue in Github: https://github.com/molbal/svcfitstat/issues"];
            }
        }

        /**
         * Handles async calculation request
         *
         * @param Request $request
         *
         * @return array
         */
        public function handleSync(Request $request) {
            [$fit, $appId, $appSecret, $fitId] = $this->extractInputFromRequest($request);

            try {
                $this->validateRequest($fit, $appId, $appSecret, $fitId);
                $this->auth->fileNewRequest($appId, $appSecret);

                ProcessFitStat::dispatchNow([
                    'fit' => $fit,
                    'externalId' => $fitId,
                    'appId' => $appId,
                    'callback' => $this->auth->getCallbackUrl($appId)
                ]);
                return ['success' => true, 'message' => "Fit calculation job dispatched to the task queue.", 'fit' => $fit,'id' => $fitId, 'expect_callback' => $this->auth->getCallbackUrl($appId)];
            }
            catch (\Exception $exc) {
                if (get_class($exc) == 'App\Exceptions\QuotaLimitException') {$this->sendErrorEmail($appId);                }
                return ['success' => false, 'message' => $exc->getMessage(), 'dispute' => "If you think this was caused by an error on our end please open an issue in Github: https://github.com/molbal/svcfitstat/issues", 'error_line' => $exc->getLine(), 'error_file' => $exc->getFile()];
            }
        }

        /**
         * @param string $fit Input EFT file
         *
         * @throws \RuntimeException Throws these if a problem arises.
         */
        private function quickValidateEft(string $fit) {

            if (strlen($fit) > 2048) {
                throw new \RuntimeException("Please input a shorter EFT file. (Less characters than 2048)");
            }

            $lines = explode("\n", $fit);
            $regex_header = '/^\\[.+\\,.+\\]$/im';

//            $lines[0] = '[Gila, Gila T4 ABYXss Electric]';
            $line = trim($lines[0]);
            if ($line[0] !== '[') {
                throw new \RuntimeException(sprintf("The first character of the first line should be '[', not '%s'", $line[0]));
            }
            if ($line[strlen($line)-1] !== ']') {
                throw new \RuntimeException(sprintf("The last character of the first line should be ']', not '%s'", $line[strlen($line)-1]));
            }
            if (stripos($line, ',') === false) {
                throw new \RuntimeException(sprintf("The first line (%s) should contain a comma", $line));
            }

            if (count($lines) < 3) {
                throw new \RuntimeException("A properly formatted EFT file is a bit longer than the given input");
            }

            if (count($lines) > 96) {
                throw new \RuntimeException("Please input a shorter EFT file. (Less lines, than 96)");
            }
        }/**
     * @param Request $request
     *
     * @return array
     */
        private function extractInputFromRequest(Request $request) : array {
            $fit = $request->get("fit");
            $appId = $request->get("appId");
            $appSecret = $request->get("appSecret");
            $fitId = $request->get("fitId") ?? "[not provided]";

            return [$fit, $appId, $appSecret, $fitId];
        }

        /**
         * Makes sure the request is valid
         * @param string $fit
         * @param string $appId
         * @param string $appSecret
         * @param string $fitId
         */
        private function validateRequest(string $fit, string $appId, string $appSecret, string $fitId) : void {
            if (!$fit) {
                throw new \RuntimeException("Fit value not set.");
            }

            if (!$appId || !$appSecret) {
                throw new \RuntimeException("Please provide both appId and appSecret" );
            }

            if (strlen(strval($fitId)) > 64) {
                throw new \RuntimeException(sprintf("Fit ID must be max 64 chars long, not %s, ya dummy", strlen(strval($fitId))));
            }

            $this->quickValidateEft($fit);
        }

        /**
         * @param $appId
         */
        private function sendErrorEmail($appId) : void {
            $mail = $this->callback->getErrorEmail($appId);
            Log::info(sprintf("Queuing error mail to %s", $mail));
            Mail::to($mail)
                ->queue(
                    new ErrorMessage(sprintf("Application %s quota was reached. It resets next month. Until then the fit calculation service is unable to process your requests.", $appId), sprintf("Application %s quota reached", $appId)));
        }
    }
