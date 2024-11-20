FROM ambarltd/courses-v2-php:v1.1

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
RUN composer install

# Windows environments running Linux Docker containers 
# make a mess of carriage returns. Let's fix it.
RUN sed 's/\r//g' run_in_local.sh > compatible.sh
RUN chmod --reference=run_in_local.sh compatible.sh
RUN mv compatible.sh run_in_local.sh

EXPOSE 8080
CMD ["./run_in_local.sh"]
