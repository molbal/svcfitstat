<?php

    namespace App\Http\Controllers;

    use App\Http\Controllers\Auth\AppAuthController;
    use App\Jobs\ProcessFitStat;
    use App\Mail\ErrorMessage;
    use App\WorkerConnector\WorkerConnector;
    use http\Exception\RuntimeException;
    use Illuminate\Http\Request;
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

            $fit = $request->get("fit");
            $appId = $request->get("appId");
            $appSecret = $request->get("appSecret");
            $fitId = $request->get("fitId") ?? "[not provided]";

            if (strlen(strval($fitId)) > 64) {
                throw new \RuntimeException(sprintf("Fit ID must be 64 long, not %s, ya dummy", strlen(strval($fitId))));
            }

            try {

                $this->quickValidateEft($fit);
                $this->auth->fileNewRequest($appId, $appSecret);

                ProcessFitStat::dispatch([
                    'fit' => $fit,
                    'externalId' => $fitId,
                    'appId' => $appId,
                    'callback' => $this->auth->getCallbackUrl($appId)
                ]);
                return ['status' => true, 'message' => "Fit calculation job dispatched to the task queue."];
            }
            catch (\Exception $exc) {
                if (get_class($exc) == 'App\Exceptions\QuotaLimitException') {
                    $mail = $this->callback->getErrorEmail($appId);

                    Mail::to($mail)->queue(
                        new ErrorMessage(
                            sprintf("Application %s quota was reached. It resets next month. Until then the fit calculation service is unable to process your requests.", $appId),
                            sprintf("Application %s quota reached", $appId)
                        )
                    );
                }
                return ['status' => false, 'message' => $exc->getMessage(), 'dispute' => "If you think this was caused by an error on our end please open an issue in Github: https://github.com/molbal/svcfitstat/issues"];
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
            $regex_header = '/^\[.+\,.+\]$/im';
            if (!preg_match($regex_header, $lines[0])) {
                throw new \RuntimeException(sprintf("The given fit's first line does not seem to be a valid EFT header. First line: <%s>. Checked using regex %s", $lines[0], $regex_header));
            }

            if (count($lines) < 3) {
                throw new \RuntimeException("A properly formatted EFT file is a bit longer than the given input");
            }

            if (count($lines) > 96) {
                throw new \RuntimeException("Please input a shorter EFT file. (Less lines, than 96)");
            }
        }
    }
