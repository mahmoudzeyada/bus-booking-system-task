## About Task

this is a hiring task. which solves bus-booking system.

### Technologies used

-   Php
-   Laravel
-   MySql
-   Nginx
-   Docker
-   Docker Compose

## Task Installation

this task have 3 docker containers one for the app , another one for the db and another for the web.

-   clone the repo and cd to repo directory.
-   make a copy of .env.example to file .env
    `cp .env.example .env`
-   for simplicity sake make copy of these variables to env file

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=your_mysql_root_password
```

-   run this command to start the containers
    `docker-compose up -d`
    -run these commands to set up app container
    `docker-compose exec app php artisan key:generate`
    `docker-compose exec app php artisan config:cache`
    `docker-compose exec app composer install`
    `docker-compose exec app php artisan migrate`
    `docker-compose exec app php artisan migrate:fresh --seed`

## Declaration

first i want to declare that is my first time ever writing php so to solve this task i focused on solving the problem mainly so i did not use some featurtes in the framework that could take my some times to learn instead of solving the problem like:

## features not used

-   depency injection in laravel.
-   Eloquent Relations.

i didnot make authorization and authentication system i choosed to solve the problem instead of just writing some boilerblat code thats takes amount of time.

i choosed rest instead of graphql because i want to avoid a the overhead for choosing and installing library for graphql and works well with laravel other wise i well choose graphql.

i choosed mysql over any relation databases because php and mysql are good friend and i choosed the one that i am not suffer for mainting db otherwise i well choose postgres.

## My Solving Description

you will find in the main directory 2 files one for usecase diagrams and modules called UseCase.drawio and Erd.drawio for database diagram

so each trip has number of stations each station has rank station are order by rank like the following:
1-cairo 0 -> start_of_line
2-mansora 1
3-dametta 2
4-bortsaid 3 -> end_of_line

so here if we want to compute number of avaliable seats in mansora we should count the number of seats in dametta and bortsaid as (start or end station) and the number of start station in mansora and substarct that with the original number of seat of the trip.

and each seat has start_station and end_station.
