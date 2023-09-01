#!/usr/bin/env bash
DIR="$(realpath ${BASH_SOURCE%/*})"

docker run --rm -ti \
           -v "${DIR}/..":/app \
           petrknap/php-spaydqr:latest \
           ${@}
