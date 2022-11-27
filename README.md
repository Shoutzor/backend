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

This is the backend of Shoutz0r, consisting of both the API and the Queue worker.

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

### Preparations
1. Go to the root of the backend project, copy `.env.default` to `.env` and make sure to edit the following variables:
   - `APP_KEY`: Run `php artisan key:generate --show` and put the generated output here
   - `DB_PASSWORD`: Optional. If you define a custom password here both the `backend` and `mysql` containers will use this password instead
   - `PUSHER_APP_SECRET` Optional. If you define a custom password here both the `backend` and `echo` containers will use this password instead
2. Run `composer install` to install all dependencies of the `backend`.
   - If you already did this before, you can skip this step.

### Install Shoutz0r
1. Open your `.env` file 
   - change `DB_HOST` to `127.0.0.1` (there's a known-issue where `localhost` will cause the connection to fail)
   - change `REDIS_HOST` to `127.0.0.1` or `localhost`
2. Run `docker compose -f docker-compose.yml -f docker-compose.dev.yml up mysql redis` and wait for MySQL to become ready.
3. In a separate terminal run `composer install-shoutzor-dev`.
   - If you want to reinstall shoutzor, you can run `composer fresh-install-shoutzor-dev` instead\
     (⚠️ **WARNING** ⚠️ This will drop **ALL TABLES**!)
3. If the installation completes, open your `.env` and change:
   - `DB_HOST` back to `mysql`
   - `REDIS_HOST` back to `redis`
4. You can now go back to the running `docker compose` command and hit `CTRL + C` to shut down those containers.

### Run Backend containers
To start the full backend, you can now run `docker compose -f docker compose.yml -f docker compose.dev.yml up` 
 - Building the images might take a while
 - This will start all required services for the `backend` and `worker` to function. After those have started, the `backend` and `worker` will be started too.
 - The `backend` and `worker` will be watching for changes and restart automatically.

 - For production environments you can run `docker compose up` instead. (Assuming Shoutz0r has been installed)

## Composer commands:

| Command                               | Explanation                                                            |
|---------------------------------------|------------------------------------------------------------------------|
| `composer install-shoutzor`           | Installs shoutzor for production environments                          |
| `composer fresh-install-shoutzor`     | ⚠️ **Drops all tables**, then installs shoutzor for production environments                          |
| `composer install-shoutzor-dev`       | Installs shoutzor for development environments (adds mock data)        |
| `composer fresh-install-shoutzor-dev` | ⚠️ **Drops all tables**, then installs shoutzor for development environments (adds mock data)        |
| `composer add-mock-data`              | Generates and adds mock data to the database using `DevelopmentSeeder` |

## Kindly supported by

* [JetBrains](https://www.jetbrains.com/?from=Shoutz0r)
* [Navicat](https://www.navicat.com/)

## Sponsor this project

Shoutz0r is being developed entirely in my spare time. \
If you like this project, please consider sponsoring it using the button in the sidebar of this repo (or [click here](https://github.com/sponsors/xorinzor) ).\
Every little bit helps to buy me a beer or pizza, which keeps me going!
