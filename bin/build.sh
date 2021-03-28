#!/usr/bin/env bash

## To commit changes of this file
# ------------------------------
# git add build.sh
# git update-index --chmod=+x build.sh
# git commit -m "Make build.sh executable."
# git push

. ./bin/parse_env.sh

docker build --no-cache -t "${CONTAINER_NAME}" -f ./docker/Dockerfile . --build-arg APP_ENV="${APP_ENV}"
docker tag "${CONTAINER_NAME}":latest registry.sheba.xyz/"${CONTAINER_NAME}":latest
docker push registry.sheba.xyz/"${CONTAINER_NAME}":latest
