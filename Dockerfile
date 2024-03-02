FROM php:8.3-apache
RUN apt update && apt install -y \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    nodejs \
    npm \
    && npm install --global yarn \ 
    && git config --global user.email "you@example.com" && git config --global user.name "Your Name" \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo_mysql
RUN docker-php-ext-enable intl mbstring zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY --from=node:current-slim /usr/local/bin /usr/local/bin
COPY --from=node:current-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && apt install -y symfony-cli

COPY apache-default.conf /etc/apache2/sites-available/000-default.conf
WORKDIR /usr/src
VOLUME /usr/src

COPY ./docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY src/ /usr/src/
RUN ls --recursive /usr/src/
RUN chmod -R 777 /usr/src/app/public/uploads
RUN chmod -R 777 /usr/src/app/ai_content
RUN chmod -R 777 /usr/src/app/var
RUN cd /usr/src/app && composer install
RUN cd /usr/src/app && npm install --global yarn
RUN cd /usr/src/app && yarn install && yarn encore production

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
