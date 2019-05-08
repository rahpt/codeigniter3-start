#!/bin/sh

## Part of CI start
##
## @author     José Proença <https://github.com/rahpt>
## @license    MIT License
## @copyright  2019 José Proença
## @link       https://github.com/rahpt/ci-start

cd `dirname $0`/..

ADDR_PORT=${1:-localhost:80}
DOC_ROOT=${2:-public}

php -S "$ADDR_PORT" -t "$DOC_ROOT/"
