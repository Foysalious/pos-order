#!/usr/bin/env bash

supervisord
service nginx start
php-fpm
