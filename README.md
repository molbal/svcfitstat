<p align="center">
<img src="public/logo_giant.png" alt="logo" width="460">
</p>

# About this project

This microservice takes a fit and returns its statistics using [CLI modified Pyfa](https://github.com/molbal/Pyfa) to do the calculations. 

The service exposes a REST webservice that is - for now - used internally. 

# Architecture
<p align="center">
<img src="public/architecture.png" alt="logo" width="720">
</p>

The service consists of an exposed API and its components (this project), and a modified Pyfa running in a docker container.

## API Service (this project)
This service was built with Laravel and handles communication, credentials check, and caching.

## Docker container
<img src="https://img.icons8.com/dusk/64/000000/docker.png"/>

The worker that actually runs the Pyfa is in a container due to its numerous dependencies: [Docker image](https://github.com/molbal/svcfitstat-worker)

## CLI modified Pyfa
<img src="https://avatars3.githubusercontent.com/u/16587622?s=200&v=4" alt="logo" width="64">

A fork of Pyfa was modified to accept a fit as a command line parameter and print its parameters. [Pyfa fork](https://github.com/molbal/Pyfa)
 
## AWS Simple Queue Service
We have two queues, one for the sync jobs, with priority, and one for the async jobs, with lower priority.

# Endpoints
All endpoints are secured by tokens and we only support HTTPS.

TBD

# Database

TBD
