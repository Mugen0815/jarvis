#!/usr/bin/env bash

if [[ "$1" == apache2* ]] || [ "$1" = 'php-fpm' ]; then
        if [ ! -e /usr/src/app/vendor ]; then
            echo "INSTALLING DEPENDENCIES with composer install"
            cd /usr/src/app && composer install
        fi    
        if [ ! -e /usr/src/app/node_modules ]; then
            echo "INSTALLING DEPENDENCIES with yarn install"
            cd /usr/src/app && yarn install
        fi    
        echo "SETTING APIKEY= $APIKEY"
        export APIKEY=$APIKEY
        echo "SETTING MODEL= $MODEL"
        export MODEL=$MODEL
        export PORTAINER_USERNAME=$PORTAINER_USERNAME
        export PORTAINER_PASSWORD=$PORTAINER_PASSWORD
        export PORTAINER_URL=$PORTAINER_URL
        export MAIL_HOST=$MAIL_HOST
        export MAIL_PORT=$MAIL_PORT
        export MAIL_USERNAME=$MAIL_USERNAME
        export MAIL_PASSWORD=$MAIL_PASSWORD
        export MAIL_FROM=$MAIL_FROM
        cd /usr/src/app && yarn encore production
fi

echo "STARTING APACHE"

exec "$@"
