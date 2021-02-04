# Hermes Project
[Hermes](https://www.rhizomatica.org/hermes/) is a High-frequency Emergency and Rural Multimedia Exchange System.



## Hermes station api
This is a REST api for use on Hermes stations to exchange messages between then,
 it uses [Lumen PHP Framework](https://lumen.laravel.com/).

## depends on a web server and php 7.3 setup

## to configure
- Setup your settings creating a .env file from .env.example
- Run:
     compose install

- To start a fresh database:
    php artisan migrate:refresh --seed

## Running on port 8000:
❯ php -S localhost:8000 -t public

## Hermes Message Pack
is a tar gziped file named .hmp

## storage local file structure paths (storage/app/)

uploads (Files of outgoing messages)
downloads (Files generated from the inbox received messages)
inbox (incoming hermes message packs)
outbox (hermes message pack for deliver)
tmp (tmp files)