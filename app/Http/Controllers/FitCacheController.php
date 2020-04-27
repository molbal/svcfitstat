<?php


	namespace App\Http\Controllers;


	use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

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
        public function getFitHash(string $fit): string {

            // Get lines
            $lines = explode("\n", $fit);

            // Get and strip the first line
            $header = explode(",", explode("[", $lines[0],2)[1], 2)[0];

            // Remove the header and then merge the rest of the items
            array_shift($lines);

            // Sort them to still cache same fits with different module orders
            sort($lines);

            // Make it back together into a string
            $fit = $header.implode(";", $lines);

            // MD5 is good enough here
            return md5($fit);
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
                    return json_decode(DB::table("long_term_cache")->where("HASH", $key)->value("VALUE"), true);
                default:
                case FitCacheController::MISS:
                    return null;
            }
        }

        /**
         * Caches the fit
         * @param string $fit
         * @param        $value
         */
        public function putCache(string $fit, array $value): void {
	        $key = $this->getFitHash($fit);
	        Cache::put(sprintf("sfs.short.%s", $key), now()->addHours(3), $value);
            DB::table("long_term_cache")->insert([
                'HASH' => $key,
                "EXPIRE" => now()->addDays(10),
                'VALUE' => json_encode($value)
            ]);
        }

        /**
         * Removes old cache entries
         */
        public function pruneCache():void {
	        DB::table("long_term_cache")->whereDate("EXPIRE", '>=', now())->delete();
        }
	}
