#!/usr/bin/env bash

supervisord
php artisan horizon:publish
service nginx start
php-fpm
