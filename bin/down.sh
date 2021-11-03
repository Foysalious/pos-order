#!/usr/bin/env bash
. ./bin/parse_env.sh
docker container rm -f "${CONTAINER_NAME}"
docker image rm "${CONTAINER_NAME}"
