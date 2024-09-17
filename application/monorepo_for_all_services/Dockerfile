FROM php:8.3-cli

RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/UTC /etc/localtime
RUN "date"

RUN apt-get update
RUN apt-get install git zip unzip -y
RUN apt-get install wget -y
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version
RUN apt-get install -y libssl-dev
RUN apt-get install -y zlib1g-dev
RUN pecl install mongodb
RUN docker-php-ext-enable mongodb
RUN apt-get install -y libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql
RUN apt-get install -y libicu-dev
RUN docker-php-ext-configure intl && docker-php-ext-install intl


# Symfony cache, logs, session directories
RUN rm -rf /symfony_tmp
RUN mkdir -p /symfony_tmp/cache/environment_production
RUN mkdir -p /symfony_tmp/logs/environment_production
RUN mkdir -p /symfony_tmp/sessions/environment_production
RUN mkdir -p /symfony_tmp/cache/environment_production/doctrine/odm/mongodb/Hydrators
RUN mkdir -p /symfony_tmp/cache/environment_staging_debug
RUN mkdir -p /symfony_tmp/logs/environment_staging_debug
RUN mkdir -p /symfony_tmp/sessions/environment_staging_debug
RUN mkdir -p /symfony_tmp/cache/environment_staging_debug/doctrine/odm/mongodb/Hydrators
RUN mkdir -p /symfony_tmp/cache/environment_test
RUN mkdir -p /symfony_tmp/logs/environment_test
RUN mkdir -p /symfony_tmp/sessions/environment_test
RUN mkdir -p /symfony_tmp/cache/environment_test/doctrine/odm/mongodb/Hydrators
RUN chmod 777 -R /symfony_tmp

WORKDIR /srv
COPY code/composer.json /srv/composer.json
COPY code/composer.lock /srv/composer.lock
RUN composer install
# vendor folder is ignored in COPY because of .dockerignore
COPY code /srv
# Prevent executing tests, which do silly things like touch the database
RUN rm /srv/tests -Rf
RUN composer install

ARG SERVICE_NAME_IN_LOWERCASE
RUN cp "/srv/config/routes_by_service_for_build_stage/routes_$SERVICE_NAME_IN_LOWERCASE.yaml" /srv/config/routes.yaml

EXPOSE 8080
CMD ["./run_inside_docker.sh"]


