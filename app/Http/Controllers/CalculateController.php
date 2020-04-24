<?php

    namespace App\Http\Controllers;

    use App\Http\Controllers\Auth\AppAuthController;
    use App\Jobs\ProcessFitStat;
    use App\WorkerConnector\WorkerConnector;
    use Illuminate\Http\Request;

    class CalculateController extends Controller {

        /** @var AppAuthController */
        protected $auth;

        /** @var WorkerConnector */
        protected $worker;


        /**
         * CalculateController constructor.
         *
         * @param AppAuthController $auth
         * @param WorkerConnector   $worker
         */
        public function __construct(AppAuthController $auth, WorkerConnector $worker) {
            $this->auth = $auth;
            $this->worker = $worker;
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

            try {

                $this->auth->fileNewRequest($appId, $appSecret);

                ProcessFitStat::dispatch(['fit' => $fit]);
                return ['status' => true, 'message' => "Fit calculation job dispatched to the task queue."];
            }
            catch (\Exception $exc) {
                return ['status' => false, 'message' => $exc->getMessage()];
            }
        }
    }
