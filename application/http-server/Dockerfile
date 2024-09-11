FROM php:7.3-cli

RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/UTC /etc/localtime
RUN "date"

RUN apt-get update
RUN apt-get install git zip unzip -y
RUN apt-get install wget -y
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version
RUN apt-get install -y  libssl-dev zlib1g-dev \
	&& pecl install mongodb \
	&& docker-php-ext-enable mongodb
RUN apt-get install -y libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

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
COPY code/vendor /srv/vendor
COPY code/composer.json /srv/composer.json
COPY code/composer.lock /srv/composer.lock
RUN composer install
COPY code /srv
RUN composer install

EXPOSE 8080
CMD [ "php", "-S", "0.0.0.0:8080", "-t", "public" ]


