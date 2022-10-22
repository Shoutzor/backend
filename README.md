# Shoutz0r (Backend)

[![License](https://img.shields.io/github/license/Shoutz0r/backend.svg?style=flat)](https://www.gnu.org/licenses/gpl-3.0.en.html)
[![CodeFactor](https://www.codefactor.io/repository/github/Shoutz0r/backend/badge/main)](https://www.codefactor.io/repository/github/Shoutz0r/backend/overview/main)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=Shoutz0r_backend&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=Shoutz0r_backend)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=Shoutz0r_backend&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=Shoutz0r_backend)

1. [Introduction](#introduction)
2. [Composer commands](#composer-commands)
3. [Building & Using the docker container](#building--using-the-docker-container)
4. [Kindly Supported by](#kindly-supported-by)
5. [Sponsor this project](#sponsor-this-project)

## Introduction

This is the backend of shoutz0r, consisting of both the API and the Queue worker.

Built using Laravel, GraphQL (Lighthouse) & Apollo.

API Docs can be found over at [shoutzor.com](https://shoutzor.com/phpdocs/app/master/). \
Documentation has yet to be written. Feel free to ask any questions in the `discussions`.

## Composer commands:

| Command                         | Explanation                                                           |
|---------------------------------|-----------------------------------------------------------------------|
| `composer install-shoutzor`     | Installs shoutzor for production environments                         |
| `composer install-shoutzor-dev` | Installs shoutzor for development environments (adds mock data)       |
| `composer add-mock-data`        | Generates and adds mock data to the database using `DevelopmentSeeder` |

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
    - For production run `composer install-shoutzor-dev` instead.

## Kindly supported by

* [JetBrains](https://www.jetbrains.com/?from=Shoutz0r)
* [Navicat](https://www.navicat.com/)

## Sponsor this project

Shoutz0r is being developed entirely in my spare time. \
If you like this project, please consider sponsoring it using the button in the sidebar of this repo (or [click here](https://github.com/sponsors/xorinzor) ).\
Every little bit helps to buy me a beer or pizza, which keeps me going!
