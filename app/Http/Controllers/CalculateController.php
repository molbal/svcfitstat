<?php

namespace App\Http\Controllers;

use App\DTO\FitStatRequest;
use App\Jobs\ProcessFitStat;
use App\WorkerConnector\WorkerConnector;
use Illuminate\Http\Request;

class CalculateController extends Controller
{

    /** @var WorkerConnector */
    protected $worker;

    /**
     * CalculateController constructor.
     *
     * @param WorkerConnector $worker
     */
    public function __construct(WorkerConnector $worker) {
        $this->worker = $worker;
    }


    public function handleSync(Request $request) {

        $fit = $request->get("fit");

        $calc = $this->worker->calculateStats($fit);

//        if (!$calc["success"]) {
//            return ["Could not calculate stats."];
//        }

        return $calc;

    }

    public function handleAsync(Request $request) {

        $fit = $request->get("fit");
        $userId = $request->get("userId");
        $fitStatRequest = (new FitStatRequest())
            ->setEft($fit)
            ->setUserId($userId)
            ->setSync(false);

        ProcessFitStat::dispatch($fitStatRequest);

        return ['status' => true, 'message' => "Fit job dispatched."];
    }
}
