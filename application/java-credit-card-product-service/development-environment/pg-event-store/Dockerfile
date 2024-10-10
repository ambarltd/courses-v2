FROM postgres:16.4

RUN mkdir -p /var/lib/postgresql/certs
RUN openssl genrsa -out /var/lib/postgresql/certs/server.key 2048
RUN chmod 600 /var/lib/postgresql/certs/server.key
RUN openssl req -new -key /var/lib/postgresql/certs/server.key -out /var/lib/postgresql/certs/server.csr -subj "/C=GB/ST=London/L=London/O=SnakeOil/OU=Org/CN=snake-oil.oil"
RUN openssl x509 -req -in /var/lib/postgresql/certs/server.csr -signkey /var/lib/postgresql/certs/server.key -out /var/lib/postgresql/certs/server.crt -days 36500
RUN chown 999 /var/lib/postgresql/certs
RUN chown 999 /var/lib/postgresql/certs -Rf