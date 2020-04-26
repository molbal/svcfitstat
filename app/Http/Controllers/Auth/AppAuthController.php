<?php


    namespace App\Http\Controllers\Auth;


    use App\Exceptions\InvalidAuthenticationException;
    use App\Exceptions\QuotaLimitException;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use RuntimeException;

    /**
     * Class AppAuthController
     *
     * Handles the calculation request filing, and authorization.
     *
     * @package App\Http\Controllers\Auth
     */
    class AppAuthController extends Controller {


        /**
         * Files the request under the given APP ID and APP SECRET
         *
         * @param string $appId     APP ID
         * @param string $appSecret APP SECRET
         *
         * @throws InvalidAuthenticationException On wrong APP ID or APP SECRET
         * @throws QuotaLimitException On reaching the monthly limit
         * @throws RuntimeException On all other issues
         */
        public function fileNewRequest(string $appId, string $appSecret) {
            try {
                if (!$this->appExists($appId)) {
                    throw new InvalidAuthenticationException(sprintf("No application registered with ID %s", $appId));
                }

                if (!$this->validateAppSecret($appId, $appSecret)) {
                    throw new InvalidAuthenticationException(sprintf("No application match for ID %s with the given secret.", $appId));
                }

                $this->fileCalculationRequest($appId);
            } catch (InvalidAuthenticationException $exception) {
                Log::warning(sprintf("Invalid authentication attempt: APP_ID=[%s] APP_SECRET=[%s]", $appId, $appSecret));
                throw $exception;
            } catch (QuotaLimitException $exception) {
                Log::warning(sprintf("Quota limit reached/exceeded for APP_ID=[%s]", $appId));

                throw $exception;
            } catch (\Exception $exception) {
                Log::warning(sprintf("Unchecked exception with: APP_ID=[%s] APP_SECRET=[%s]", $appId, $appSecret));
                throw new \RuntimeException("Unchecked exception with: APP_ID=[%s] APP_SECRET=[%s]", 500, $exception);
            }
        }

        /**
         * Gets the callback URL for
         * @param string $appId
         *
         * @return mixed
         */
        public function getCallbackUrl(string $appId) {
            return Cache::remember("sfs.callback.$appId", now()->addHour(), function () use ($appId) {
               return DB::table("applications")
                   ->where("APP_ID", $appId)
                   ->value("CALLBACK_URL");
            });
        }

        /**
         * Gets if a specified application exists. Result is cached for an hour.
         *
         * @param string $appId
         *
         * @return bool
         */
        private function appExists(string $appId) : bool {
            return Cache::remember(sprintf("sfc.app.exists.%s", $appId), now()->addHour(), function () use ($appId) {
                return DB::table("applications")
                         ->where("APP_ID", $appId)
                         ->exists();
            });
        }

        /**
         * Gets is a specified application id and secret pair is correct. Result is cached for an hour.
         *
         * @param string $appId
         * @param string $appSecret
         *
         * @return bool
         */
        private function validateAppSecret(string $appId, string $appSecret) : bool {
            return Cache::remember(sprintf("sfc.app.key.correct.%s", md5($appId . $appSecret)), now()->addHour(), function () use ($appId, $appSecret) {
                return DB::table("applications")
                         ->where("APP_ID", $appId)
                         ->where("APP_SECRET", $appSecret)
                         ->exists();
            });
        }

        /**
         * Files a new calculation so it is counted against the quota
         *
         * @param string $appId
         *
         * @throws QuotaLimitException Throws QuotaLimitException when the allowed calculation count has been exceeded
         * @throws \Exception Throws when DB issue
         */
        private function fileCalculationRequest(string $appId) : void {
            $max = Cache::remember(sprintf("svf.app.%s.max", $appId), now()->addHour(), function () use ($appId) {
                return DB::table("applications")
                         ->where("APP_ID", $appId)
                         ->value("QUOTA");
            });
            DB::beginTransaction();
            try {
                $current = DB::table("rolling")
                             ->where("APP_ID", $appId)
                             ->count();
                if ($current >= $max) {
                    throw new QuotaLimitException(sprintf("Application %s used up %d out of %d allowed fit calculations therefore the service cannot take your job. The fits number resets every month.", $appId, $current, $max));
                }
                DB::table("rolling")
                  ->insert(["APP_ID" => $appId]);
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                throw $exception;
            }
        }
    }
