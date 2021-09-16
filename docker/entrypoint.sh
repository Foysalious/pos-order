#!/usr/bin/env bash

supervisord
php artisan horizon
service nginx start
php-fpm
