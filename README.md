<p align="center">
<img src="https://svcfitstat.eve-nt.uk/logo_giant.png" alt="logo" width="460">
</p>

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
so many threads open, when a fit calculation result can come minutes later. So to combat this, we are using a ***solicit/response*** type of communication, which, in this 
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

## Receive a response
When setting up an URL the site will 


## Caching
To the outside, caching is not transparent. But under the hood, we have 2 caching solutions working in parallel: After calculation, a fit is cached for 3 hours in memory, 
using Redis, and for 10 days in the database.


## Database

<p align="center">
<img src="https://svcfitstat.eve-nt.uk/schema.png" alt="logo" width="600">
</p>

The database has 2 functions now: credentials and caching.


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
