#!/bin/sh

## To commit changes of this file
# ------------------------------
# git add deploy.sh
# git update-index --chmod=+x deploy.sh
# git commit -m "Make deploy.sh executable."
# git push

get_git_branch() {
    # shellcheck disable=SC2005
    echo "$(git symbolic-ref --short -q HEAD 2>/dev/null)"
}

pull_from_docker_registry() {
    . ./bin/parse_env.sh
    docker pull registry.sheba.xyz/"${CONTAINER_NAME}"

    ./bin/dcup.sh prod -d
    ./bin/composer.sh install --no-interaction --ignore-platform-reqs
    ./bin/pre_deployment_script_for_all_branch.sh
    # ./bin/sentry_release_with_redis_entry_script.sh
}

# USE ON LOCAL
run_on_local() {
    . ./bin/parse_env.sh
    ./bin/dcup.sh local -d
    ./bin/composer.sh install
    ./bin/composer.sh update
    ./bin/pre_deployment_script_for_all_branch.sh
}

# USE ON DEVELOPMENT
run_on_development() {
    git fetch origin
    reset="sudo git reset --hard origin/"
    reset_branch="$reset$1"
    eval "${reset_branch}"

    . ./bin/parse_env.sh
    ./bin/dcup.sh dev -d

    ./bin/composer.sh install --no-interaction --ignore-platform-reqs
    ./bin/composer.sh update
    ./bin/config_clear.sh
    ./bin/pre_deployment_script_for_all_branch.sh
}

branch=$1
if [ -z "${branch}" ]; then
    branch="$(get_git_branch)"
fi

if [ "${branch}" = "master" ]; then
    pull_from_docker_registry
elif [ "${branch}" = "development" ]; then
    run_on_development "${branch}"
elif [ "${branch}" = "local" ]; then
    run_on_local
fi
