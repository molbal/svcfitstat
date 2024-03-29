## Discontinuation notice

Dear Capsuleers,

It is with regret that I write to inform you that the pyfa-cli and svcfitstat projects are being discontinued. The reason for this decision is the transfer of the related Abyss Tracker project to EVE Workbench.

It has been an honor and a privilege to work on this project and I would like to express my sincere gratitude to all of you who have used and supported svcfitstat. Your feedback and contributions have been invaluable and have helped shape the project into what it is today.

Although this marks the end of this project, I am confident that the move of the Abyss Tracker to EVE Workbench will lead to even greater success and advancements in the future. I encourage you to continue to follow the developments on EVE Workbench and support the ongoing efforts of the new maintainers.

Thank you again for your support and understanding.

Fly safe,

Veetor

## About this project

This microservice takes a fit and returns its statistics using [CLI modified Pyfa](https://github.com/molbal/Pyfa) to do the calculations. 
The service exposes a REST webservice that is - for now - used internally for other EVE_NT, +10 Gaming and Veetor's services.

### Contribution
If you would like to fire up the container, go for it, this page should explain the components. The other option is to use this this (reference) installation, thenrea contact me
and we will figure something out. 

## Architecture
<p align="center">
<img src="https://svcfitstat.eve-nt.uk/architecture.png" alt="logo" width="720">
</p>

The service consists of an exposed API and its components (this project), and a modified Pyfa running in a docker container. The following paragraphs will explain or link to the
components.

### API Service (this project)
This service was built with Laravel and handles communication, credentials check, and caching.

### Docker container
The worker that actually runs the Pyfa is in a container due to its numerous dependencies. For now, we only support one thread.
For more information, please check the [related GitHub project.](https://github.com/molbal/svcfitstat-worker)

### CLI modified Pyfa
A fork of Pyfa modified a substantially to accept a fit as a command line parameter and print its parameters. [Pyfa fork](https://github.com/molbal/Pyfa)
 
### AWS Simple Queue Service
Incoming calculation requests are dispatched into a queue where a service worker runs Pyfa to calculate. Due to the time consuming nature of this service, it would be unwise to keep 
so many threads open, when a fit calculation result can come minutes later. So to combat this, we are using an async ***request/response*** type of communication, which, in this 
implementation simply means that your application will send in a request via a POST request, and when the worker processed your request, the API will call an endpoint you provide 
to return the fit stats.

## Dispatch a job
You can dispatch a job at this endpoint. 
HTTP Method:
```
POST
```
URL:
```
https://svcfitstat.eve-nt.uk/api/calculate/async
```
Headers:
```
Accept: application/json
Content-Type: application/x-www-form-urlencoded
```
The following form fields should be set:
           
|Name|Description|Mandatory|
|---|---|---|
|`fit`|Fit in EFT format. Please do your best to ensure that only valid fits are get sent to the worker.|Yes|
|`appId`|Your application ID|Yes|
|`appSecret`|Your application secret|Yes|
|`fitId`|An ID that will be passed back in the response.|No|

For this request you will immediately receive a response whether it was added to the job queue, or not. The answer will be a JSON string, which will contain the following values:

|Key|Type|Content|
|---|---|---|
|`success`|boolean|Whether the job dispatch succeeded|
|`message`|string|If success is false, it contains the error message|

## Receive a response
When setting up an URL the site will call that with a POST message.

The post message contains the following fields:

|Field name|Description|
|---|---|
|`auth`|sha1 hash of the app secret|
|`id`|The ID you entered in the `fitId` param of the initial request|
|`result`|JSON encoded array ([Example response](CALLBACK_EXAMPLE.md))|

### Example callback receiver function
This snippet is from a Laravel api.php router file:

```php
Route::post("fit/callback", function (Request $request) {

    // This is your app secret
    $app_secret = env("FIT_SERVICE_APP_SECRET");

    // This is the thing we will receive
    $expectedAuth = sha1($app_secret);

    // Valudate auth code
    if ($request->get("auth") != $expectedAuth) {
        return response(['error' => 'Invalid auth code provided.'], 403);
    }

    // Get the ID and the data
    $id = $request->get("id");
    $data = $request->get("result");

    // Print into the log
    Log::info(sprintf(
            "Callback received for fit %s: \n%s",
            $id,
            print_r($data, 1)
    ));
});
```

Which prints the following to the log file: [Example](CALLBACK_EXAMPLE.md)


## Caching
To the outside, caching is not transparent. But under the hood, we have 2 caching solutions working in parallel: After calculation, a fit is cached for 3 hours in memory, 
using Redis, and for 10 days in the database.

## Database

<p align="center">
<img src="https://svcfitstat.eve-nt.uk/schema.png" alt="logo" width="600">
</p>

The database has 2 functions now: credentials and caching. 

### DB Caching 
Each entries return array is serialized into a json string, its EFT is made into a hash and then store in the `long_term_cache` table for 10 days.
Every day a scheduled task runs to delete expired log entries.

### Authorization and quota enforcement
Applications are tied to users, but this is not used in any way now. 
Applications have an APP_ID and an APP_SECRET, which are - for now - stored as clear text (I know I know) in the database. 
This is where the callback URL is also stored along with the monthly quota.

After validating incoming requests a row is inserted into the `rolling` table. 
Each month a scheduled task empties the monthly table thus resets the quota. When it runs it inserts a row into the `history` table. 


## FAQ:
##### Does fit name count with caching?
No, two identical fits with different names will still be cached.

##### Does module order count with caching?
No, the same fits with different module orders will count as one.

##### Can I see if a fit was cached, or not?
No, this information is not transparent

##### What is the maximum cache time?
Fits are cached for up to 10 days.

##### Is there a way to clear caches earlier?
No, but when major balances changes happen, the cache will be manually purged. (For example [Surgical Strike patch](https://www.eveonline.com/article/q8f2l8/surgical-strike-coming-15-april))
 
##### What is the way to contact the application maintainer?
In a [Github Issue](https://github.com/molbal/svcfitstat/issues), on [Twitter](https://twitter.com/veetor_in_eve), on [Patreon](https://www.patreon.com/veetor)

##### How long does it take to process a fit?
A single fit is processed in about 10 seconds.

##### What are future plans with this service?
If people would like to use this, I would like to expand the number of workers to allow more than 1 job processing at a time. However, this cannot be done without increasing our 
current capacity. 

##### Why can't you just send the request like most other web services, why do I need to build a receiving endpoint?
Keeping a server thread open for many open requests at the same time would use too much memory, potentially affecting other services on this EC2 instance. 
