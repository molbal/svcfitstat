<?php


    namespace App\Http\Controllers;


    use App\Exceptions\CallbackStatusException;
    use App\Mail\ErrorMessage;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Mail;

    class CallbackController {

        public function doCallback(string $url, string $externalId, string $appId, $data) : void {
            try {
                $r = Http::timeout(10)
                    ->post($url, [
                        "result" => $data,
                        "id" => $externalId,
                        "auth" => $this->getAuthHash($appId)]);

                if (!$r->ok()) {
                    throw new CallbackStatusException(sprintf(sprintf("URL %s returned %%s for %s, application %s - If we get a status other than 200, this message is sent.", $url, $externalId, $appId), $r->status()));
                }
            } catch (ConnectionException $exc) {
                $message = sprintf("Could not connect to callback URL %s which was set for application %s because it timed out. The fit service will not attempt to call the URL again. This affects ID %s", $url, $appId, $externalId);
                Log::warning("Sending error message: ".$message);
                $this->notifyAppMaintainer($appId, $message, "Callback URL timed out");
            } catch (CallbackStatusException $e) {
                $message = $e->getMessage();
                Log::warning("Sending error message: ".$message);
                $this->notifyAppMaintainer($appId, $message, "Callback URL returned non 200 status code");
            }
            catch (\Exception $e) {
                $message = "We ran into an unknown issue while callbacking:";
                // TODO CONTINUE FROM HERE
            }

        }

        /**
         * Notifies the application maintainer about an issue with the callback url
         * @param string $appId App ID
         * @param string $message Message
         */
        private function notifyAppMaintainer(string $appId, string $message, string $subject): void {
            try {
                $mail = $this->getErrorEmail($appId);
                Mail::to($mail)->queue(new ErrorMessage($message, $subject));
            }
            catch (\Exception $e) {
                Log::error(sprintf("This is the end. Could not send an error message about the error: %s", $e->getMessage()));
            }
        }

        /**
         * Calculates hash code
         * @param string $appId app ID
         *
         * @return string hashed app secret
         */
        private function getAuthHash(string $appId): string {
            $appSecret = Cache::remember("sfs.appsecret.$appId", now()->addHour(), function() use ($appId) {
                return DB::table("applications")->where("APP_ID", $appId)->value("APP_SECRET");
            });

            return sha1($appId);
        }
        /**
         * Calculates hash code
         * @param string $appId app ID
         *
         * @return string hashed app secret
         */
        private function getErrorEmail(string $appId): string {
            return Cache::remember("sfs.notify.$appId", now()->addMinutes(15), function() use ($appId) {
                return DB::table("applications")->where("NOTIFY_EMAIL", $appId)->value("APP_SECRET");
            });

        }
    }
