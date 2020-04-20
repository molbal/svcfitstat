<?php

    namespace App\DTO;
    use Illuminate\Database\Eloquent\Model;

    class FitStatRequest extends Model {

        /** @var int */
        public $id;

        /** @var string */
        public $eft;

        /** @var int */
        public $userId;

        /** @var bool */
        public $sync;

        /**
         * @return string
         */
        public function getEft() : string {
            return $this->eft;
        }

        /**
         * @param string $eft
         *
         * @return FitStatRequest
         */
        public function setEft(string $eft) : FitStatRequest {
            $this->eft = $eft;

            return $this;
        }

        /**
         * @return int
         */
        public function getUserId() : int {
            return $this->userId;
        }

        /**
         * @param int $userId
         *
         * @return FitStatRequest
         */
        public function setUserId(int $userId) : FitStatRequest {
            $this->userId = $userId;

            return $this;
        }

        /**
         * @return bool
         */
        public function isSync() : bool {
            return $this->sync;
        }

        /**
         * @param bool $sync
         *
         * @return FitStatRequest
         */
        public function setSync(bool $sync) : FitStatRequest {
            $this->sync = $sync;

            return $this;
        }

    }
