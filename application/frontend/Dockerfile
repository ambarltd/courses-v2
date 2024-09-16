FROM php:8.3-cli

WORKDIR /srv
RUN mkdir /srv -p
RUN mkdir /srv/public -p
COPY public /srv/public

EXPOSE 8080
CMD [ "php", "-S", "0.0.0.0:8080", "-t", "public" ]


