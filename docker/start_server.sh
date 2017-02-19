#!/bin/sh

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo $DIR

docker rm -f phalcon-template
docker-compose rm

docker-compose build phalcon-template
WEB_ID=$(docker-compose up -d phalcon-template)

