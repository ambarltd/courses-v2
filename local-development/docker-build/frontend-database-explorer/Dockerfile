FROM node:20.11.1

# Define build arguments
ARG DB_TYPE=postgres
ARG STUDIO_PORT=5555

WORKDIR /app

COPY package.json .
RUN npm install
RUN npm install prisma-dbml-generator

COPY prisma-mongodb ./prisma-mongodb/
COPY prisma-postgres ./prisma-postgres/

RUN mkdir prisma && \
    if [ "$DB_TYPE" = "mongo" ]; then \
      cp prisma-mongodb/schema.prisma prisma/schema.prisma; \
    else \
      cp prisma-postgres/schema.prisma prisma/schema.prisma; \
    fi

# Create a start script that handles schema introspection and generation
RUN echo '#!/bin/sh\n\
refresh_schema() {\n\
  echo "Refreshing schema..."\n\
  OLD_MD5=$(md5sum prisma/schema.prisma)\n\
  npx prisma db pull --force\n\
  npx prisma generate\n\
  NEW_MD5=$(md5sum prisma/schema.prisma)\n\
  if [ $? -eq 0 ]; then\n\
    if [ "$OLD_MD5" != "$NEW_MD5" ]; then\n\
      echo "Schema changed. Schema reload needed. Exiting to trigger container restart."\n\
      exit 1\n\
    fi\n\
    echo "Schema unchanged."\n\
  else\n\
    echo "Failed to refresh schema. Exiting to trigger container restart."\n\
    exit 1\n\
  fi\n\
}\n\
\n\
if [ "$DB_TYPE" = "mongo" ]; then \n\
  export DATABASE_URL="$MONGO_DATABASE_URL"\n\
  echo "MongoDB detected - initializing..."\n\
  npx prisma db pull --force\n\
  npx prisma generate\n\
  npx prisma studio --port $STUDIO_PORT --hostname 0.0.0.0 & \n\
  echo "Prisma studio running. Proceeding to periodically refresh schema." \n\
  # Avoid modifying the existing client code to prevent bugs when refreshing schema.\n\
  cp /app /tmp/app_temp -Rf\n \
  cp /tmp/app_temp /var/local/app_temp -Rf\n \
  cd /var/local/app_temp\n \
  while true; do\n\
    echo "Waiting 10 seconds before next schema refresh..."\n\
    sleep 10\n\
    refresh_schema\n\
  done\n\
else \n\
  export DATABASE_URL="$POSTGRES_DATABASE_URL"\n\
  npx prisma generate\n\
  npx prisma studio --port $STUDIO_PORT --hostname 0.0.0.0\n\
fi' > /app/start.sh

RUN chmod +x /app/start.sh

ENV POSTGRES_DATABASE_URL=""
ENV MONGO_DATABASE_URL=""
EXPOSE $STUDIO_PORT

CMD ["/app/start.sh"]