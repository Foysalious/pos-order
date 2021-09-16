#!/usr/bin/env bash

. ./bin/parse_env.sh

# shellcheck disable=SC2124
start_horizon_script="docker exec --user www-data ${CONTAINER_NAME} php artisan horizon &"
eval "${start_horizon_script}"
