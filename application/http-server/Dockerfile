FROM php:8.3-cli

COPY code /srv
WORKDIR /srv

EXPOSE 8080
CMD [ "php", "-S", "0.0.0.0:8080", "-t", "public" ]


