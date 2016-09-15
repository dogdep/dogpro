# Before you start

You need to setup some third party services:
 - Bitbucket/Github/Gitlab or any other authentication methods
 - Pusher (https://pusher.com) for instant release information
 - Slack (optional) if you want to get notifications
 
When you obtain api keys for these services, put them in the `.env` file.

# Installation

There are two ways you can setup DogPro:
 - using docker-compose
 - manualy
 
Setting up with docker-compose is much easier and recomended way.
 
## Docker setup

Docker setup is a lot easier:
  - Install docker (https://docs.docker.com/installation)
  - Install docker compose (https://docs.docker.com/compose/install)
  - Clone repository `git clone git@github.com:dogdep/dogpro.git`
  - Add configuration `cp .env.example .env` and modify if you need
  - Add docker-compose config `cp docker-compose.yml.dist docker-compose.yml`
  - Modify it `docker-compose.yml` according to your needs (you should probably want to change ports)
  - Run `docker-compose up -d`
  - That is it
  
## Manual setup

To setup manually there are several things you need on the server:
  - HTTP server (nginx - see example config in `docker/nginx/nginx.conf`) 
  - MySQL
  - Git installed on the system (at least v1.8)
  - Ansible installed on the system (v1.9)
  
After you install all of these and project is reachable in your browser, run these commands:
  - `cp .env.example .env`
  - `bower install`
  - `npm install`
  - `gulp`
  - `composer install`
  - `php artisan key:generate`
  - `php artisan migrate`

Also you should run queue worker, see [laravel docs](http://laravel.com/docs/5.1/queues#running-the-queue-listener) on how to setup it.
  
That is it.
  
