# Shoutz0r (Backend)

[![License](https://img.shields.io/github/license/Shoutz0r/backend.svg?style=flat)](https://www.gnu.org/licenses/gpl-3.0.en.html)
[![CodeFactor](https://www.codefactor.io/repository/github/Shoutz0r/backend/badge/main)](https://www.codefactor.io/repository/github/Shoutz0r/backend/overview/main)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=Shoutz0r_backend&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=Shoutz0r_backend)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=Shoutz0r_backend&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=Shoutz0r_backend)

1. [Introduction](#introduction)
2. [Development](#development)
3. [Composer commands](#composer-commands)
4. [Building & Using the docker container](#building--using-the-docker-container)
5. [Kindly Supported by](#kindly-supported-by)
6. [Sponsor this project](#sponsor-this-project)

## Introduction

This is the backend of shoutz0r, consisting of both the API and the Queue worker.

Built using Laravel, GraphQL (Lighthouse) & Apollo.

API Docs can be found over at [shoutzor.com](https://shoutzor.com/phpdocs/app/master/). \
Documentation has yet to be written. Feel free to ask any questions in the `discussions`.

## Development

For local development you have 2 options (that I know of):
- Install PHP (8.1) locally
    - With the MySQL driver
    - With the Redis driver
    - With the [OpenSwoole extension](https://openswoole.com/docs/get-started/installation)
- Mount code directory to the backend container

The method I will describe below assumes you have PHP installed locally.

1. In the root of the backend project, copy `.env.default` to `.env` and make sure to edit the following variables:
    - `APP_KEY`: Run `php artisan key:generate --show` and use it's value
    - `DB_HOST`: Set this to `127.0.0.1`
    - `DB_PASSWORD`: Optional. If you define a custom password here both the `backend` and `mysql` containers will use this password instead
    - `PUSHER_HOST` If you will be doing any laravel echo development, point this to the `echo` server
    - `PUSHER_APP_SECRET` If you will be doing any laravel echo development, define your `echo` server password here
    
2. Open the terminal and navigate to the root of the backend project
3. Run `docker-compose -f docker-compose.testing.yml up mysql redis` 
    - This will start the `mysql` and `redis` containers that are required for the backend to work; if you need any other containers to run, just add them to the list after `up`
4. Open another terminal and navigate to the root of the backend project
5. Run `composer install` to install all dependencies of the `backend`. 
    - If you already did this before, you can skip this step.
6. Run `composer install-shoutzor-dev` to install shoutzor
    - If you want to reinstall shoutzor, you can run `composer fresh-install-shoutzor-dev` instead (⚠️ **WARNING** ⚠️ This will drop **ALL TABLES**!)
7. You can now run `php artisan octane:start --watch` to start the backend. Any changes you make will reload the server automatically

## Composer commands:

| Command                               | Explanation                                                            |
|---------------------------------------|------------------------------------------------------------------------|
| `composer install-shoutzor`           | Installs shoutzor for production environments                          |
| `composer fresh-install-shoutzor`     | ⚠️ **Drops all tables**, then installs shoutzor for production environments                          |
| `composer install-shoutzor-dev`       | Installs shoutzor for development environments (adds mock data)        |
| `composer fresh-install-shoutzor-dev` | ⚠️ **Drops all tables**, then installs shoutzor for development environments (adds mock data)        |
| `composer add-mock-data`              | Generates and adds mock data to the database using `DevelopmentSeeder` |

## Building & Using the docker container
1. run `composer install` on your local machine
    - For production use `composer install --no-dev`
2. Now you can build & run the dockerfile
    - It's recommended to perform all actions using `docker-compose`. \
    You can execute commands via `docker-compose -f docker-compose.testing.yml run backend your_command_here` where `your_command_here` will be executed on the backend container.\
    For more information you can check the [docker-compose documentation](https://docs.docker.com/compose/).
3. Make sure to configure the environment variables before running the containers
    - No `APP_KEY` yet? Run `php artisan key:generate --show` and use it's value
    - Multiple backend containers? Make sure you configure the same `APP_KEY` for them
4. Haven't installed shoutzor yet? 
    - Run `composer install-shoutzor-dev` on the `backend` container.
    - For production run `composer install-shoutzor` instead.
    - If the installation fails and throws errors about tables already existing you can either manually drop the tables or use the `fresh-install` variants of the `install` commands instead. Be aware that the `fresh-install` variants will **drop all tables** ⚠️

## Kindly supported by

* [JetBrains](https://www.jetbrains.com/?from=Shoutz0r)
* [Navicat](https://www.navicat.com/)

## Sponsor this project

Shoutz0r is being developed entirely in my spare time. \
If you like this project, please consider sponsoring it using the button in the sidebar of this repo (or [click here](https://github.com/sponsors/xorinzor) ).\
Every little bit helps to buy me a beer or pizza, which keeps me going!
