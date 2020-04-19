<?php

    use Illuminate\Database\Eloquent\Model;

    class FitStatRequest extends Model {

        /** @var string */
        public $eft;

        /** @var int */
        public $userId;

        /** @var bool */
        public $sync;

    }
