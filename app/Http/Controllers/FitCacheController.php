<?php


	namespace App\Http\Controllers;


	use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;

    class FitCacheController {

        const MISS = 0;
        const HIT_MEMORY = 1;
        const HIT_DB = 2;

        /**
         * Gets the hash of the fit, which will act as an ID
         * @param string $fit EFT string
         *
         * @return string Hash
         */
        protected function getFitHash(string $fit): string {
            return md5($fit); // Good enough here
	    }

        /**
         * Gets if a fit exists in one of the caches
         *
         * @param string $fit EFT string
         *
         * @return int: MISS = 0, HIT_MEMORY = 1, HIT_DB = 2
         */
        protected function checkCaches(string $fit): int {
            $key = $this->getFitHash($fit);

            // Check Redis
            if (Cache::has(sprintf("sfs.short.%s", $key))) {
                return FitCacheController::HIT_MEMORY;
            }

            if (DB::table("long_term_cache")->where("HASH", $key)->exists()) {
                return FitCacheController::HIT_DB;
            }

            return FitCacheController::MISS;
	    }

        /**
         * @param string $fit
         *
         * @return mixed|null
         */
	    public function getCacheValue(string $fit) {
            $key = $this->getFitHash($fit);
            switch ($this->checkCaches($fit)) {
                case FitCacheController::HIT_MEMORY:
                    return Cache::get(sprintf("sfs.short.%s", $key));
                case FitCacheController::HIT_DB:
                    return DB::table("long_term_cache")->where("HASH", $key)->value("VALUE");
                default:
                case FitCacheController::MISS:
                    return null;
            }
        }

        /**
         * Removes old cache entries
         */
        public function pruneCache():void {
	        DB::table("long_term_cache")->whereDate("EXPIRE", '>=', now())->delete();
        }
	}
