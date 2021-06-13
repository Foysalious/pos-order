#!/usr/bin/env bash

. ./bin/parse_env.sh
compose_folder="./docker/composes"
compose="docker-compose --env-file=./.env -f $compose_folder/docker-compose.yml"

if [[ $1 = "prod" ]]; then
  extra="-f $compose_folder/docker-compose.prod.yml"
elif [[ $1 = "dev" ]]; then
  extra="-f $compose_folder/docker-compose.dev.yml"
elif [[ $1 = "local" ]]; then
  extra="-f $compose_folder/docker-compose.local.yml"
fi

compose="$compose $extra down"
eval "${compose}"
