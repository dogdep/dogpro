# Before you start

You need to setup some third party services:
 - Bitbucket/Github/Gitlab or any other authentication methods
 - Pusher (https://pusher.com) for instant release information
 - Slack (optional) if you want to getn notifications
 
When you obtain api keys for these services, put them in the `.env` file.

# Installation

There are two ways you can setup DogPro:
 - using docker-compose
 - manualy
 
Setting up with docker-compose is much easyer and recomended way.
 
## Docker setup

Docker setup is a lot easyer:
  - Install docker (https://docs.docker.com/installation)
  - Install docker compose (https://docs.docker.com/compose/install)
  - Clone repository `git clone git@github.com:dogdep/dogpro.git`
  - Add configuration `cp .env.example .env` and modyfy if you need
  - Add docker-compose config `cp docker-compose.yml.dist docker-compose.yml`
  - Modify it `docker-compose.yml` according to your needs (you should probably want to change ports)
  - Run `docker-compose up -d`
  - That is it
  
## Manual setup

To setup manually there are things you need on the server:
  - Http server (nginx - see example config in `docker/nginx/nginx.conf`) 
  - MySQL
  - Git installed on the system (at lease 1.8)
  - Ansible installed on the system (1.9)
  
After you installed all of these and project is reachable in your browser, run these commands:
  - `cp .env.example .env`
  - `composer install`  
  - `php artisan key:generate`
  - `php artisan migrate`
  
That is it.
  
