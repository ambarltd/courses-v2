FROM php:8.3-fpm

RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/UTC /etc/localtime
RUN "date"

RUN apt-get update
RUN apt-get install git -y
RUN apt-get install zip -y
RUN apt-get install unzip -y
RUN apt-get install libssl-dev -y
RUN apt-get install zlib1g-dev -y
RUN apt-get install wget -y
RUN apt-get install nginx -y
# to use top in the container when necessary
RUN apt-get install procps -y
RUN apt-get install supervisor -y

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version
RUN pecl install mongodb
RUN docker-php-ext-enable mongodb
RUN apt-get install -y libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql
RUN apt-get install -y libicu-dev
RUN docker-php-ext-configure intl && docker-php-ext-install intl


RUN mkdir -p /symfony_tmp/cache/
RUN mkdir -p /symfony_tmp/cache/production/
RUN mkdir -p /symfony_tmp/cache/production/doctrine/odm/mongodb/Proxies/
RUN mkdir -p /symfony_tmp/cache/production/doctrine/odm/mongodb/Hydrators/
RUN mkdir -p /symfony_tmp/cache/production/doctrine/odm/mongodb/PersistentCollections/
RUN mkdir -p /symfony_tmp/cache/test/
RUN mkdir -p /symfony_tmp/cache/test/doctrine/odm/mongodb/Proxies/
RUN mkdir -p /symfony_tmp/cache/production/doctrine/odm/mongodb/Hydrators/
RUN mkdir -p /symfony_tmp/cache/production/doctrine/odm/mongodb/PersistentCollections/
RUN mkdir -p /symfony_tmp/logs/
RUN mkdir -p /symfony_tmp/logs/production/
RUN mkdir -p /symfony_tmp/logs/test/
RUN mkdir -p /symfony_tmp/sessions/
RUN mkdir -p /symfony_tmp/sessions/production/
RUN mkdir -p /symfony_tmp/sessions/test/
RUN chmod 777 -R /symfony_tmp

WORKDIR /srv
COPY code/composer.json /srv/composer.json
COPY code/composer.lock /srv/composer.lock
RUN composer install

COPY server-config/nginx.conf /etc/nginx/nginx.conf
COPY server-config/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY server-config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir /var/run/php
RUN chmod 777 /var/run/php

# vendor folder is ignored in COPY because of .dockerignore
COPY code /srv
# Prevent executing tests, which do silly things like touch the database
RUN rm /srv/tests -Rf
RUN composer install

ARG SERVICE_NAME_IN_LOWERCASE
RUN cp "/srv/config/routes_by_service_for_build_stage/routes_$SERVICE_NAME_IN_LOWERCASE.yaml" /srv/config/routes.yaml

EXPOSE 8080
CMD ["./run_in_production.sh"]
